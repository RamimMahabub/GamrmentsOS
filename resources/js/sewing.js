export function registerSewing(Alpine) {
    Alpine.data('sewingForm', ({available, rows, step}) => ({
        available,
        step,
        rows: rows.map(row => ({
            ...row,
            [`${step}_altered_pieces`]: row[`${step}_altered_pieces`] ?? 0,
            [`${step}_rejected_pieces`]: row[`${step}_rejected_pieces`] ?? (row.rejected_pieces ?? 0),
        })),
        search: '',
        lotFilter: '',
        diaFilter: '',
        colorFilter: '',

        get isQc() {
            return ['qc_phase_1', 'qc_phase_2'].includes(step);
        },
        get lots() {
            return [...new Set(this.available.map(item => item.lot).filter(Boolean))].sort();
        },
        get dias() {
            return [...new Set(this.available.map(item => String(item.dia)).filter(Boolean))].sort((a, b) => Number(a) - Number(b));
        },
        get colors() {
            return [...new Set(this.available.map(item => item.color).filter(Boolean))].sort();
        },
        get filteredAvailable() {
            const q = this.search.trim().toLowerCase();
            return this.available.filter(item => {
                if (this.lotFilter && String(item.lot) !== String(this.lotFilter)) return false;
                if (this.diaFilter && String(item.dia) !== String(this.diaFilter)) return false;
                if (this.colorFilter && String(item.color) !== String(this.colorFilter)) return false;
                if (q) {
                    const match = `${item.lot || ''} ${item.color || ''} DIA ${item.dia || ''}`.toLowerCase();
                    if (!match.includes(q)) return false;
                }
                return true;
            });
        },
        get colorGroups() {
            const groups = new Map();
            for (const item of this.filteredAvailable) {
                if (!groups.has(item.color)) groups.set(item.color, {color: item.color, rows: []});
                groups.get(item.color).rows.push(item);
            }
            return [...groups.values()];
        },
        availableLabel(item) {
            if (!item) return '';
            const doz = Number(item.dozens || 0);
            const pcs = Number(item.pieces || 0);
            if (doz > 0 && pcs > 0) {
                return `${doz} dozen + ${pcs} pieces`;
            }
            if (doz > 0) {
                return `${doz} dozen`;
            }
            return `${pcs} pieces`;
        },
        cutSummary(item) {
            if (!item) return '';
            let cutStr = (item.cut_total_dozens || 0) + ' doz';
            if (item.cut_total_remainder > 0) cutStr += ' ' + item.cut_total_remainder + ' pcs';
            let usedStr = (item.used_dozens || 0) + ' doz';
            if (item.used_remainder > 0) usedStr += ' ' + item.used_remainder + ' pcs';
            if (item.is_first_step) {
                return `of ${cutStr} cut · ${usedStr} in sewing/packed`;
            }
            return `${usedStr} already processed`;
        },
        allSelected(items) {
            return items.length > 0 && items.every(item => this.selected(item));
        },
        partiallySelected(items) {
            const count = items.filter(item => this.selected(item)).length;
            return count > 0 && count < items.length;
        },
        toggleAll(items, checked) {
            if (checked) {
                for (const item of items) {
                    if (!this.selected(item)) {
                        this.rows.push({
                            group_key: item.group_key,
                            source_body_row_id: item.source_body_row_id,
                            source_neck_row_id: item.source_neck_row_id,
                            [`${step}_dozens`]: item.dozens ?? 0,
                            [`${step}_pieces`]: item.pieces ?? 0,
                            [`${step}_altered_pieces`]: 0,
                            [`${step}_rejected_pieces`]: 0,
                            rejected_pieces: 0,
                        });
                    }
                }
            } else {
                const keys = new Set(items.map(i => String(i.group_key)));
                this.rows = this.rows.filter(row => !keys.has(String(row.group_key)));
            }
        },
        selected(source) { return this.rows.some(row => String(row.group_key) === String(source.group_key)); },
        selectedRow(source) { return this.rows.find(row => String(row.group_key) === String(source.group_key)); },
        toggle(source) {
            const index = this.rows.findIndex(row => String(row.group_key) === String(source.group_key));
            if (index >= 0) {
                this.rows.splice(index, 1);
                return;
            }
            this.rows.push({
                group_key: source.group_key,
                source_body_row_id: source.source_body_row_id,
                source_neck_row_id: source.source_neck_row_id,
                [`${step}_dozens`]: source.dozens ?? 0,
                [`${step}_pieces`]: source.pieces ?? 0,
                [`${step}_altered_pieces`]: 0,
                [`${step}_rejected_pieces`]: 0,
                rejected_pieces: 0,
            });
        },
        syncQcPassed(row, sourceItem = null, event = null) {
            if (!row) return;
            if (event && event.target) {
                const name = event.target.name || '';
                if (name.includes('altered_pieces')) {
                    row[`${step}_altered_pieces`] = event.target.value;
                } else if (name.includes('rejected_pieces')) {
                    row[`${step}_rejected_pieces`] = event.target.value;
                }
            }
            const src = sourceItem || this.source(row);
            if (!src) return;
            const totalAvail = Number(src.total_pieces ?? ((Number(src.dozens || 0) * 12) + Number(src.pieces || 0)));
            const altered = Math.max(0, parseInt(row[`${step}_altered_pieces`] || 0, 10) || 0);
            const rejected = Math.max(0, parseInt(row[`${step}_rejected_pieces`] || 0, 10) || 0);

            const passedPieces = Math.max(0, totalAvail - altered - rejected);
            row[`${step}_dozens`] = Math.floor(passedPieces / 12);
            row[`${step}_pieces`] = passedPieces % 12;
        },
        totalReleased(row) {
            if (!row) return 0;
            const doz = Number(row[`${step}_dozens`] || 0);
            const pcs = Number(row[`${step}_pieces`] || 0);
            return (doz * 12) + pcs;
        },
        source(row) { return this.available.find(item => String(item.group_key) === String(row.group_key)) || {}; },
        component(row, type) { return (this.source(row)[type] || []).find(item => String(item.id) === String(row[`source_${type === 'necks' ? 'neck' : 'rib'}_row_id`])) || {}; },
        invalid(row) {
            if (!row.group_key) return true;
            if (this.isQc) {
                const passedDoz = Number(row[`${step}_dozens`] || 0);
                const passedPcs = Number(row[`${step}_pieces`] || 0);
                const altered = Number(row[`${step}_altered_pieces`] || 0);
                const rejected = Number(row[`${step}_rejected_pieces`] || 0);
                if (passedDoz < 0 || passedPcs < 0 || altered < 0 || rejected < 0) return true;
                return (passedDoz + passedPcs + altered + rejected) <= 0;
            }
            return String(row[`${step}_dozens`] ?? '').trim() === '' || Number(row[`${step}_dozens`]) < 0;
        },
    }));
}

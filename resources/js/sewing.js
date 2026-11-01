export function registerSewing(Alpine) {
    Alpine.data('sewingForm', ({available, rows, step}) => ({
        available,
        rows: rows.map(row => ({...row})),
        get colorGroups() {
            const groups = new Map();
            for (const item of this.available) {
                if (!groups.has(item.color)) groups.set(item.color, {color: item.color, rows: []});
                groups.get(item.color).rows.push(item);
            }
            return [...groups.values()];
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
                [`${step}_dozens`]: '',
                [`${step}_pieces`]: 0,
                rejected_pieces: 0,
            });
        },
        source(row) { return this.available.find(item => String(item.group_key) === String(row.group_key)) || {}; },
        component(row, type) { return (this.source(row)[type] || []).find(item => String(item.id) === String(row[`source_${type === 'necks' ? 'neck' : 'rib'}_row_id`])) || {}; },
        invalid(row) { return !row.group_key || String(row[`${step}_dozens`] ?? '').trim() === ''; },
    }));
}

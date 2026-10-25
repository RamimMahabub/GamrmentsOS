import { mkdir, readFile, writeFile } from 'node:fs/promises';

const composerLock = JSON.parse(await readFile(new URL('../composer.lock', import.meta.url), 'utf8'));
const livewirePackage = composerLock.packages.find((dependency) => dependency.name === 'livewire/livewire');
const sourceReference = livewirePackage?.source?.reference;

if (livewirePackage?.source?.url !== 'https://github.com/livewire/livewire.git' || !/^[a-f0-9]{40}$/.test(sourceReference ?? '')) {
    throw new Error('The Composer lock file must contain a pinned Livewire source reference.');
}

const destinationDirectory = new URL('../vendor/livewire/livewire/dist/', import.meta.url);
await mkdir(destinationDirectory, { recursive: true });

await Promise.all(['livewire.esm.js', 'livewire.esm.js.map'].map(async (filename) => {
    const response = await fetch(`https://raw.githubusercontent.com/livewire/livewire/${sourceReference}/dist/${filename}`, {
        signal: AbortSignal.timeout(30_000),
    });

    if (!response.ok) {
        throw new Error(`Unable to download the locked Livewire asset ${filename}: HTTP ${response.status}.`);
    }

    await writeFile(new URL(filename, destinationDirectory), new Uint8Array(await response.arrayBuffer()));
}));

console.log(`Prepared frontend assets for Livewire ${livewirePackage.version}.`);

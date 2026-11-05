const fs = require('fs');

const rawPath = 'storage/app/public/indonesia.svg';
const outPath = 'resources/views/public/partials/home/indonesia-map.blade.php';

const raw = fs.readFileSync(rawPath, 'utf8');
const paths = raw.match(/<path[^>]*>/g) || [];
const processed = paths.map(p => p.replace(/<path/, '<path class="map-island"'));

const svg = `<svg class="id-map" viewBox="0 29 793 288" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
    <defs>
        <style>
            .map-island { fill: #0C0A09; stroke: none; }
        </style>
    </defs>
${processed.join('\n')}
</svg>`;

fs.writeFileSync(outPath, svg);
console.log(`Wrote ${svg.length} bytes to ${outPath}`);

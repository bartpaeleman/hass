const fs = require('fs');
const svg = fs.readFileSync('IMGS/MyHouse.svg', 'utf8');

let newSvg = svg.replace(/<polygon[^>]+points="([^"]+)"[^>]*>/g, (match, points) => {
    let pts = points.trim().split(/\s+/);
    let d = 'M ' + pts[0] + ' ' + pts[1];
    for (let i = 2; i < pts.length; i += 2) {
        if (pts[i] && pts[i+1]) {
            d += ' L ' + pts[i] + ' ' + pts[i+1];
        }
    }
    d += ' Z';
    let path = match.replace('polygon', 'path').replace(/points="[^"]+"/, `d="${d}"`);
    return path;
});

newSvg = newSvg.replace(/<rect[^>]+x="([^"]+)"\s+y="([^"]+)"\s+width="([^"]+)"\s+height="([^"]+)"[^>]*>/g, (match, x, y, w, h) => {
    x = parseFloat(x);
    y = parseFloat(y);
    w = parseFloat(w);
    h = parseFloat(h);
    let d = `M ${x} ${y} L ${x+w} ${y} L ${x+w} ${y+h} L ${x} ${y+h} Z`;
    let path = match.replace('rect', 'path').replace(/x="[^"]+"\s+y="[^"]+"\s+width="[^"]+"\s+height="[^"]+"/, `d="${d}"`);
    return path;
});

fs.writeFileSync('IMGS/MyHouse_paths.svg', newSvg);

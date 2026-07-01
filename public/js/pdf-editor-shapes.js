// Shapes tab actions: drawShape, addHorizontalLine, addVerticalLine, addStar, addCheckmark, addCrossmark, toggleFreeDraw, eraseObject, addTriangle, addHexagon, addPentagon, addOctagon, addHeart, addCloud, addBarChart, addPieChart, addLineChart, addAreaChart, addScatterChart, addRoundedRect, addParallelogram, addDiamond, addCrescent, addBookmark, addBracket, addCurlyBrace

window.drawShape = function(type) {
    const canvas = window.getCanvas();
    if (!canvas) return;
    let obj = null;
    switch (type) {
        case 'rect':
            obj = new fabric.Rect({ left: 120, top: 120, width: 100, height: 60, fill: 'rgba(0,0,0,0.07)', stroke: '#333', strokeWidth: 2 });
            break;
        case 'circle':
            obj = new fabric.Circle({ left: 180, top: 120, radius: 40, fill: 'rgba(0,0,255,0.07)', stroke: '#333', strokeWidth: 2 });
            break;
        case 'line':
            obj = new fabric.Line([100, 100, 200, 100], { stroke: "#333", strokeWidth: 2 });
            break;
        case 'arrow':
            obj = new fabric.Polyline([{ x: 0, y: 10 }, { x: 60, y: 10 }, { x: 50, y: 0 }, { x: 60, y: 10 }, { x: 50, y: 20 }], {
                left: 300, top: 300, fill: '', stroke: '#333', strokeWidth: 3
            });
            break;
        case 'polygon':
            obj = new fabric.Polygon([{ x: 50, y: 0 }, { x: 100, y: 40 }, { x: 80, y: 100 }, { x: 20, y: 100 }, { x: 0, y: 40 }], {
                left: 250, top: 200, fill: 'rgba(255,0,0,0.1)', stroke: '#333', strokeWidth: 2
            });
            break;
        case 'ellipse':
            obj = new fabric.Ellipse({ left: 200, top: 200, rx: 60, ry: 30, fill: 'rgba(0,0,255,0.1)', stroke: '#333', strokeWidth: 2 });
            break;
        case 'triangle':
            obj = new fabric.Triangle({ left: 220, top: 220, width: 80, height: 70, fill: 'rgba(255,255,0,0.1)', stroke: '#333', strokeWidth: 2 });
            break;
        case 'hexagon': {
            const points = [];
            const cx = 350, cy = 250, r = 40;
            for (let i = 0; i < 6; i++) {
                points.push({ x: cx + r * Math.cos(Math.PI / 3 * i), y: cy + r * Math.sin(Math.PI / 3 * i) });
            }
            obj = new fabric.Polygon(points, { fill: 'rgba(0,255,255,0.1)', stroke: '#333', strokeWidth: 2 });
            break;
        }
        case 'pentagon': {
            const points = [];
            const cx = 350, cy = 250, r = 40;
            for (let i = 0; i < 5; i++) {
                points.push({ x: cx + r * Math.cos((2 * Math.PI * i) / 5 - Math.PI / 2), y: cy + r * Math.sin((2 * Math.PI * i) / 5 - Math.PI / 2) });
            }
            obj = new fabric.Polygon(points, { fill: 'rgba(255,0,255,0.1)', stroke: '#333', strokeWidth: 2 });
            break;
        }
        case 'octagon': {
            const points = [];
            const cx = 350, cy = 250, r = 40;
            for (let i = 0; i < 8; i++) {
                points.push({ x: cx + r * Math.cos((2 * Math.PI * i) / 8), y: cy + r * Math.sin((2 * Math.PI * i) / 8) });
            }
            obj = new fabric.Polygon(points, { fill: 'rgba(0,255,0,0.1)', stroke: '#333', strokeWidth: 2 });
            break;
        }
        case 'heart':
            obj = new fabric.Path('M 272 377 Q 272 337 312 337 Q 352 337 352 377 Q 352 417 312 457 Q 272 417 272 377 Z', {
                left: 200, top: 200, fill: 'pink', stroke: '#d32f2f', strokeWidth: 2, scaleX: 0.2, scaleY: 0.2
            });
            break;
        case 'cloud':
            obj = new fabric.Path('M60,60 Q40,20 80,20 Q120,20 100,60 Q140,60 140,100 Q140,140 100,140 Q60,140 60,100 Q20,100 20,60 Q20,20 60,60 Z', {
                left: 200, top: 200, fill: '#e0f2fe', stroke: '#0288d1', strokeWidth: 2, scaleX: 0.6, scaleY: 0.6
            });
            break;
        case 'arrowedLine':
            obj = new fabric.Group([
                new fabric.Line([0, 0, 80, 0], { stroke: "#333", strokeWidth: 3 }),
                new fabric.Triangle({ left: 80, top: 0, width: 16, height: 16, angle: 90, fill: "#333", originX: 'center', originY: 'center' })
            ], { left: 300, top: 300 });
            break;
        default:
            return;
    }
    canvas.add(obj);
    canvas.setActiveObject(obj);
};

window.addHorizontalLine = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const line = new fabric.Line([50, 400, 400, 400], {
        stroke: "#333", strokeWidth: 2
    });
    canvas.add(line);
    canvas.setActiveObject(line);
};

window.addVerticalLine = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const line = new fabric.Line([100, 100, 100, 400], {
        stroke: "#333", strokeWidth: 2
    });
    canvas.add(line);
    canvas.setActiveObject(line);
};

window.addStar = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const points = [];
    const cx = 500, cy = 100, spikes = 5, outerRadius = 30, innerRadius = 12;
    let rot = Math.PI / 2 * 3, x = cx, y = cy;
    let step = Math.PI / spikes;
    for (let i = 0; i < spikes; i++) {
        x = cx + Math.cos(rot) * outerRadius;
        y = cy + Math.sin(rot) * outerRadius;
        points.push({ x, y });
        rot += step;
        x = cx + Math.cos(rot) * innerRadius;
        y = cy + Math.sin(rot) * innerRadius;
        points.push({ x, y });
        rot += step;
    }
    const star = new fabric.Polygon(points, {
        left: 500, top: 100, fill: 'rgba(255,215,0,0.2)', stroke: '#fbc02d', strokeWidth: 2
    });
    canvas.add(star);
    canvas.setActiveObject(star);
};

window.addCheckmark = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const poly = new fabric.Polyline([
        { x: 0, y: 10 }, { x: 10, y: 20 }, { x: 30, y: 0 }
    ], {
        left: 350, top: 350, fill: '', stroke: '#388e3c', strokeWidth: 4
    });
    canvas.add(poly);
    canvas.setActiveObject(poly);
};

window.addCrossmark = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const line1 = new fabric.Line([0, 0, 20, 20], { left: 400, top: 400, stroke: "#d32f2f", strokeWidth: 4 });
    const line2 = new fabric.Line([20, 0, 0, 20], { left: 400, top: 400, stroke: "#d32f2f", strokeWidth: 4 });
    const group = new fabric.Group([line1, line2]);
    canvas.add(group);
    canvas.setActiveObject(group);
};

let isDrawingMode = false;
window.toggleFreeDraw = function () {
    if (!window.activeCanvas) return;
    isDrawingMode = !isDrawingMode;
    window.activeCanvas.isDrawingMode = isDrawingMode;
    if (window.showInfo) window.showInfo('Freehand drawing ' + (isDrawingMode ? 'enabled' : 'disabled'));
};

window.eraseObject = function() {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const obj = canvas.getActiveObject();
    if (obj) {
        canvas.remove(obj);
    }
};

window.addTriangle = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const triangle = new fabric.Triangle({
        left: 220, top: 220, width: 80, height: 70, fill: 'rgba(255,255,0,0.1)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(triangle);
    canvas.setActiveObject(triangle);
};

window.addHexagon = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const points = [];
    const cx = 350, cy = 250, r = 40;
    for (let i = 0; i < 6; i++) {
        points.push({
            x: cx + r * Math.cos(Math.PI / 3 * i),
            y: cy + r * Math.sin(Math.PI / 3 * i)
        });
    }
    const hex = new fabric.Polygon(points, {
        fill: 'rgba(0,255,255,0.1)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(hex);
    canvas.setActiveObject(hex);
};

window.addPentagon = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const points = [];
    const cx = 350, cy = 250, r = 40;
    for (let i = 0; i < 5; i++) {
        points.push({
            x: cx + r * Math.cos((2 * Math.PI * i) / 5 - Math.PI / 2),
            y: cy + r * Math.sin((2 * Math.PI * i) / 5 - Math.PI / 2)
        });
    }
    const pent = new fabric.Polygon(points, {
        fill: 'rgba(255,0,255,0.1)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(pent);
    canvas.setActiveObject(pent);
};

window.addOctagon = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const points = [];
    const cx = 350, cy = 250, r = 40;
    for (let i = 0; i < 8; i++) {
        points.push({
            x: cx + r * Math.cos((2 * Math.PI * i) / 8),
            y: cy + r * Math.sin((2 * Math.PI * i) / 8)
        });
    }
    const oct = new fabric.Polygon(points, {
        fill: 'rgba(0,255,0,0.1)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(oct);
    canvas.setActiveObject(oct);
};

window.addHeart = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const heart = new fabric.Path('M 272 377 Q 272 337 312 337 Q 352 337 352 377 Q 352 417 312 457 Q 272 417 272 377 Z', {
        left: 200, top: 200, fill: 'pink', stroke: '#d32f2f', strokeWidth: 2, scaleX: 0.2, scaleY: 0.2
    });
    canvas.add(heart);
    canvas.setActiveObject(heart);
};

window.addCloud = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const cloud = new fabric.Path('M60,60 Q40,20 80,20 Q120,20 100,60 Q140,60 140,100 Q140,140 100,140 Q60,140 60,100 Q20,100 20,60 Q20,20 60,60 Z', {
        left: 200, top: 200, fill: '#e0f2fe', stroke: '#0288d1', strokeWidth: 2, scaleX: 0.6, scaleY: 0.6
    });
    canvas.add(cloud);
    canvas.setActiveObject(cloud);
};

// --- Chart/Graph Tools ---

window.addBarChart = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter comma-separated values (e.g. 10,20,15,30):',
            inputs: [{ id: 'values', label: 'Bar Values', type: 'text', value: '10,20,15,30' }],
            onOk: ({ values }) => {
                const vals = values.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v));
                if (!vals.length) return;
                const max = Math.max(...vals);
                const barW = 30, gap = 12, baseX = 100, baseY = 300, chartH = 120;
                vals.forEach((v, i) => {
                    const h = (v / max) * chartH;
                    const rect = new fabric.Rect({
                        left: baseX + i * (barW + gap),
                        top: baseY + chartH - h,
                        width: barW,
                        height: h,
                        fill: '#1976d2',
                        stroke: '#333',
                        strokeWidth: 1,
                        selectable: true
                    });
                    canvas.add(rect);
                });
                canvas.renderAll();
            }
        });
    }
};

window.addPieChart = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter comma-separated values (e.g. 10,20,15,30):',
            inputs: [{ id: 'values', label: 'Pie Values', type: 'text', value: '10,20,15,30' }],
            onOk: ({ values }) => {
                const vals = values.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v));
                if (!vals.length) return;
                const sum = vals.reduce((a, b) => a + b, 0);
                let startAngle = 0;
                const cx = 300, cy = 200, r = 60;
                const colors = ['#1976d2', '#388e3c', '#fbc02d', '#d32f2f', '#7b1fa2', '#0288d1'];
                vals.forEach((v, i) => {
                    const angle = (v / sum) * 2 * Math.PI;
                    const endAngle = startAngle + angle;
                    const path = new fabric.Path(
                        `M ${cx} ${cy} L ${cx + r * Math.cos(startAngle)} ${cy + r * Math.sin(startAngle)} A ${r} ${r} 0 ${(angle > Math.PI) ? 1 : 0} 1 ${cx + r * Math.cos(endAngle)} ${cy + r * Math.sin(endAngle)} Z`,
                        { fill: colors[i % colors.length], stroke: '#333', strokeWidth: 1 }
                    );
                    canvas.add(path);
                    startAngle = endAngle;
                });
                canvas.renderAll();
            }
        });
    }
};

window.addLineChart = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter comma-separated values (e.g. 10,20,15,30):',
            inputs: [{ id: 'values', label: 'Line Values', type: 'text', value: '10,20,15,30' }],
            onOk: ({ values }) => {
                const vals = values.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v));
                if (vals.length < 2) return;
                const max = Math.max(...vals);
                const min = Math.min(...vals);
                const chartW = 180, chartH = 100, baseX = 100, baseY = 350;
                const points = vals.map((v, i) => ({
                    x: baseX + (i * (chartW / (vals.length - 1))),
                    y: baseY + chartH - ((v - min) / (max - min || 1)) * chartH
                }));
                const poly = new fabric.Polyline(points, {
                    fill: '',
                    stroke: '#1976d2',
                    strokeWidth: 3
                });
                canvas.add(poly);
                canvas.renderAll();
            }
        });
    }
};

window.addAreaChart = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter comma-separated values (e.g. 10,20,15,30):',
            inputs: [{ id: 'values', label: 'Area Values', type: 'text', value: '10,20,15,30' }],
            onOk: ({ values }) => {
                const vals = values.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v));
                if (vals.length < 2) return;
                const max = Math.max(...vals);
                const min = Math.min(...vals);
                const chartW = 180, chartH = 100, baseX = 100, baseY = 350;
                const points = vals.map((v, i) => ({
                    x: baseX + (i * (chartW / (vals.length - 1))),
                    y: baseY + chartH - ((v - min) / (max - min || 1)) * chartH
                }));
                points.push({ x: baseX + chartW, y: baseY + chartH });
                points.push({ x: baseX, y: baseY + chartH });
                const poly = new fabric.Polygon(points, {
                    fill: 'rgba(25,118,210,0.2)',
                    stroke: '#1976d2',
                    strokeWidth: 2
                });
                canvas.add(poly);
                canvas.renderAll();
            }
        });
    }
};

window.addScatterChart = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter pairs (x,y) as 10:20,30:40,50:60:',
            inputs: [{ id: 'pairs', label: 'Scatter Points', type: 'text', value: '10:20,30:40,50:60' }],
            onOk: ({ pairs }) => {
                const pts = pairs.split(',').map(p => p.split(':').map(Number)).filter(p => p.length === 2 && !isNaN(p[0]) && !isNaN(p[1]));
                if (!pts.length) return;
                const baseX = 100, baseY = 350, scale = 2;
                pts.forEach(([x, y]) => {
                    const circ = new fabric.Circle({
                        left: baseX + x * scale,
                        top: baseY + 100 - y * scale,
                        radius: 5,
                        fill: '#d32f2f',
                        stroke: '#333',
                        strokeWidth: 1
                    });
                    canvas.add(circ);
                });
                canvas.renderAll();
            }
        });
    }
};

// --- Extra shapes (dummy implementations) ---
window.addRoundedRect = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const rect = new fabric.Rect({
        left: 120, top: 120, width: 100, height: 60, rx: 20, ry: 20, fill: 'rgba(0,0,0,0.07)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(rect);
    canvas.setActiveObject(rect);
};

window.addParallelogram = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const points = [{x:20,y:0},{x:120,y:0},{x:100,y:60},{x:0,y:60}];
    const poly = new fabric.Polygon(points, {
        left: 120, top: 120, fill: 'rgba(0,0,0,0.07)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(poly);
    canvas.setActiveObject(poly);
};

window.addDiamond = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const points = [{x:60,y:0},{x:120,y:30},{x:60,y:60},{x:0,y:30}];
    const poly = new fabric.Polygon(points, {
        left: 120, top: 120, fill: 'rgba(0,0,0,0.07)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(poly);
    canvas.setActiveObject(poly);
};

window.addCrescent = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    // Dummy crescent using two circles
    const c1 = new fabric.Circle({ left: 200, top: 200, radius: 40, fill: '#ffe082' });
    const c2 = new fabric.Circle({ left: 220, top: 200, radius: 40, fill: '#fff', opacity: 0.9 });
    const group = new fabric.Group([c1, c2]);
    canvas.add(group);
    canvas.setActiveObject(group);
};

window.addBookmark = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const rect = new fabric.Rect({ left: 120, top: 120, width: 40, height: 80, fill: '#90cdf4', stroke: '#333', strokeWidth: 2 });
    const tri = new fabric.Triangle({ left: 120, top: 180, width: 40, height: 20, fill: '#3182ce', stroke: '#333', strokeWidth: 2 });
    const group = new fabric.Group([rect, tri]);
    canvas.add(group);
    canvas.setActiveObject(group);
};

window.addBracket = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    // Dummy bracket using path
    const path = new fabric.Path('M 10 10 Q 0 40 10 70', { left: 120, top: 120, stroke: '#333', strokeWidth: 3, fill: '', scaleY: 1.5 });
    canvas.add(path);
    canvas.setActiveObject(path);
};

window.addCurlyBrace = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    // Dummy curly brace using path
    const path = new fabric.Path('M 10 10 Q 0 30 10 50 Q 20 70 10 90', { left: 120, top: 120, stroke: '#333', strokeWidth: 3, fill: '', scaleY: 1.2 });
    canvas.add(path);
    canvas.setActiveObject(path);
};

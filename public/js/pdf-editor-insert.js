window.addText = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const fontSize = document.getElementById('font-size') ? parseInt(document.getElementById('font-size').value) : 18;
    const fill = document.getElementById('font-color') ? document.getElementById('font-color').value : '#000';
    const text = new fabric.IText("New Text", { left: 100, top: 100, fontSize, fill });
    canvas.add(text);
    canvas.setActiveObject(text);
};

window.addImage = function (event) {
    if (!event || !event.target || !event.target.files) {
        if (!document.getElementById('pdf-image-upload')) {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.style.display = 'none';
            input.id = 'pdf-image-upload';
            document.body.appendChild(input);
            input.addEventListener('change', function (event) {
                window.addImage(event);
                input.value = '';
            });
        }
        document.getElementById('pdf-image-upload').click();
        return;
    }
    const canvas = window.getCanvas();
    if (!canvas) return;
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = evt => {
        fabric.Image.fromURL(evt.target.result, img => {
            img.set({ left: 100, top: 100, scaleX: 0.5, scaleY: 0.5 });
            canvas.add(img);
            canvas.setActiveObject(img);
        });
    };
    reader.readAsDataURL(file);
};

window.addImageFromUrl = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter image URL:',
            inputs: [{ id: 'url', label: 'Image URL', type: 'text' }],
            onOk: ({ url }) => {
                if (!url) return;
                fabric.Image.fromURL(url, img => {
                    img.set({ left: 100, top: 100, scaleX: 0.5, scaleY: 0.5 });
                    canvas.add(img);
                    canvas.setActiveObject(img);
                }, { crossOrigin: 'anonymous' });
            }
        });
    }
};

window.addTable = function () {
    window.showModal({
        message: 'Enter table size:',
        okText: 'Insert Table',
        noteText: 'After positioning the table, click "Delete" to remove the movable frame. You can then edit the cells directly.',
        inputs: [
            { id: 'rows', label: 'Rows', type: 'number', value: 3 },
            { id: 'cols', label: 'Columns', type: 'number', value: 3 }
        ],
        onOk: function (values) {
            const canvas = window.getCanvas();
            if (!canvas) return;

            const rows = parseInt(values.rows);
            const cols = parseInt(values.cols);
            if (isNaN(rows) || isNaN(cols) || rows <= 0 || cols <= 0) {
                alert("Invalid rows or columns.");
                return;
            }

            const cellW = 80, cellH = 40;
            const headerW = 40, headerH = 30;
            const startX = 100, startY = 100;

            const tableItems = [];
            const textCells = [];
            const rowHeaders = [];
            const colHeaders = [];

            let tableLeft = startX;
            let tableTop = startY;

            const fullWidth = headerW + cols * cellW;
            const fullHeight = headerH + rows * cellH;

            const background = new fabric.Rect({
                left: tableLeft,
                top: tableTop,
                width: fullWidth,
                height: fullHeight,
                fill: 'rgba(0,0,0,0.01)',
                hasControls: true,
                hasBorders: true,
                stroke: '#999',
                strokeDashArray: [3, 3],
                selectable: true,
                objectCaching: false
            });

            let prevLeft = tableLeft;
            let prevTop = tableTop;

            background.on('moving', function () {
                const dx = background.left - prevLeft;
                const dy = background.top - prevTop;

                [...tableItems, ...textCells, ...rowHeaders, ...colHeaders].forEach(obj => {
                    obj.left += dx;
                    obj.top += dy;
                    obj.setCoords();
                });

                prevLeft = background.left;
                prevTop = background.top;

                canvas.renderAll();
            });

            // Column headers (A, B, C...)
            for (let c = 0; c < cols; c++) {
                const label = new fabric.Text(String.fromCharCode(65 + c), {
                    left: tableLeft + headerW + c * cellW + cellW / 2,
                    top: tableTop + 5,
                    fontSize: 14,
                    originX: 'center',
                    selectable: false,
                    fill: '#555'
                });
                colHeaders.push(label);
                canvas.add(label);
            }

            // Row headers (1, 2, 3...)
            for (let r = 0; r < rows; r++) {
                const label = new fabric.Text((r + 1).toString(), {
                    left: tableLeft + 10,
                    top: tableTop + headerH + r * cellH + cellH / 2,
                    fontSize: 14,
                    originY: 'center',
                    selectable: false,
                    fill: '#555'
                });
                rowHeaders.push(label);
                canvas.add(label);
            }

            // Table cells
            for (let r = 0; r < rows; r++) {
                for (let c = 0; c < cols; c++) {
                    const left = tableLeft + headerW + c * cellW;
                    const top = tableTop + headerH + r * cellH;

                    const rect = new fabric.Rect({
                        left,
                        top,
                        width: cellW,
                        height: cellH,
                        fill: '#fff',
                        stroke: '#ccc',
                        strokeWidth: 0.5,
                        selectable: false
                    });

                    const text = new fabric.IText('', {
                        left: left + 5,
                        top: top + cellH / 2,
                        width: cellW - 10,
                        height: cellH,
                        fontSize: 14,
                        fill: '#000',
                        originY: 'center',
                        originX: 'left',
                        padding: 5,
                        textAlign: 'left',
                        hasControls: false,
                        lockMovementX: true,
                        lockMovementY: true,
                        selectable: true
                    });

                    rect.on('mousedown', function () {
                        canvas.setActiveObject(text);
                        text.enterEditing();
                        setTimeout(() => {
                            if (text.hiddenTextarea) text.hiddenTextarea.focus();
                        }, 0);
                    });

                    tableItems.push(rect);
                    textCells.push(text);
                    canvas.add(rect);
                    canvas.add(text);
                }
            }

            canvas.add(background);
            canvas.setActiveObject(background);

            // Bring headers to front
            [...rowHeaders, ...colHeaders].forEach(header => {
                canvas.bringToFront(header);
            });

            canvas.renderAll();
        }
    });
};

// Helper for continuable lists (fix: after Enter, caret is after prefix, not before user text)
function makeContinuableList(prefix, initialLines, left, top) {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const getPrefix = typeof prefix === 'function' ? prefix : () => prefix;

    const textbox = new fabric.Textbox(
        initialLines.map(() => getPrefix()).join('\n'),
        {
            left, top, fontSize: 18, fill: '#000', selectable: true, width: 220
        }
    );

    function handleKeyDown(e) {
        if (e.key === 'Enter') {
            const selStart = textbox.selectionStart;
            const selEnd = textbox.selectionEnd;
            const before = textbox.text.slice(0, selStart);
            const after = textbox.text.slice(selEnd);

            // Find the end of the current line (after caret)
            const nextLineBreak = textbox.text.indexOf('\n', selEnd);
            let afterLine = '';
            let afterRest = '';
            if (nextLineBreak === -1) {
                afterLine = after;
                afterRest = '';
            } else {
                afterLine = textbox.text.slice(selEnd, nextLineBreak);
                afterRest = textbox.text.slice(nextLineBreak);
            }

            // If there is text after the caret on the current line, move it to the new line after the prefix
            let moveToNextLine = '';
            let keepAfter = '';
            if (afterLine.length > 0) {
                // Only move up to the next line break
                moveToNextLine = afterLine;
                keepAfter = afterRest;
            } else {
                moveToNextLine = '';
                keepAfter = after;
            }

            const insert = '\n' + getPrefix() + moveToNextLine;
            textbox.text = before + insert + keepAfter;
            // Place caret after prefix (and after any moved text)
            textbox.selectionStart = textbox.selectionEnd = before.length + insert.length;
            textbox.canvas && textbox.canvas.requestRenderAll();
            e.preventDefault();
        }
        // Prevent deleting prefix at start of line
        if (
            (e.key === 'Backspace' || e.key === 'Delete') &&
            textbox.selectionStart === textbox.selectionEnd
        ) {
            const sel = textbox.selectionStart;
            const textUpToSel = textbox.text.slice(0, sel);
            const lines = textUpToSel.split('\n');
            const lineIdx = lines.length - 1;
            const lineStart = textbox.text.lastIndexOf('\n', sel - 1) + 1;
            const prefixStr = getPrefix();
            if (
                (e.key === 'Backspace' && sel > 0 && sel <= lineStart + prefixStr.length) ||
                (e.key === 'Delete' && sel < textbox.text.length && sel >= lineStart && sel < lineStart + prefixStr.length)
            ) {
                e.preventDefault();
            }
        }
    }

    textbox.on('editing:entered', function () {
        const upperCanvas = textbox.canvas && textbox.canvas.upperCanvasEl;
        if (upperCanvas) {
            upperCanvas.addEventListener('keydown', handleKeyDown);
        }
    });
    textbox.on('editing:exited', function () {
        const upperCanvas = textbox.canvas && textbox.canvas.upperCanvasEl;
        if (upperCanvas) {
            upperCanvas.removeEventListener('keydown', handleKeyDown);
        }
    });

    // Always fix prefixes after any change (for paste, etc.)
    function fixPrefixes() {
        let lines = textbox.text.split('\n');
        let changed = false;
        for (let i = 0; i < lines.length; i++) {
            let expected = getPrefix();
            if (!lines[i].startsWith(expected)) {
                lines[i] = lines[i].replace(/^(• |[0-9]+\. |☐ |– |[IVXLCDM]+\.\s|[A-Z]\.\s)?/, '');
                lines[i] = expected + lines[i];
                changed = true;
            }
        }
        if (changed) {
            const sel = textbox.selectionStart;
            textbox.text = lines.join('\n');
            textbox.selectionStart = textbox.selectionEnd = sel;
        }
    }
    fixPrefixes();
    textbox.on('changed', fixPrefixes);

    canvas.add(textbox);
    canvas.setActiveObject(textbox);
}

// Use static prefix for all lists (no counting)
window.addBulletList = function () {
    makeContinuableList('• ', ['Item 1'], 100, 100);
};
window.addNumberList = function () {
    makeContinuableList('1. ', ['Item 1'], 100, 180);
};
window.addCheckboxList = function () {
    makeContinuableList('☐ ', ['Item 1'], 100, 260);
};
window.addDashList = function () {
    makeContinuableList('– ', ['Item 1'], 100, 340);
};
window.addRomanList = function () {
    makeContinuableList('I. ', ['Item 1'], 100, 420);
};
window.addAlphaList = function () {
    makeContinuableList('A. ', ['Item 1'], 100, 500);
};

window.addQRCode = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    let group = new fabric.Group([
        new fabric.Rect({ left: 0, top: 0, width: 40, height: 40, fill: "#000" }),
        new fabric.Rect({ left: 10, top: 10, width: 20, height: 20, fill: "#fff" }),
        new fabric.Rect({ left: 15, top: 15, width: 10, height: 10, fill: "#000" }),
    ], { left: 300, top: 300 });
    canvas.add(group);
    canvas.setActiveObject(group);
};

window.addBarcode = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    let group = new fabric.Group([
        new fabric.Rect({ left: 0, top: 0, width: 6, height: 40, fill: "#000" }),
        new fabric.Rect({ left: 8, top: 0, width: 3, height: 40, fill: "#fff" }),
        new fabric.Rect({ left: 13, top: 0, width: 6, height: 40, fill: "#000" }),
        new fabric.Rect({ left: 21, top: 0, width: 3, height: 40, fill: "#fff" }),
        new fabric.Rect({ left: 26, top: 0, width: 6, height: 40, fill: "#000" }),
    ], { left: 300, top: 300 });
    canvas.add(group);
    canvas.setActiveObject(group);
};

window.pageNumbersVisible = false;

window.togglePageNumber = function (btn) {
    if (!window.fabricCanvases || window.fabricCanvases.length === 0) return;

    if (!window.pageNumberObjects) window.pageNumberObjects = [];

    if (!window.pageNumbersVisible) {
        // Add page numbers
        window.pageNumberObjects = [];

        window.fabricCanvases.forEach((canvas, index) => {
            const pageIndex = index + 1;
            const text = new fabric.IText("Page " + pageIndex, {
                left: 40,
                top: canvas.height - 60,
                fontSize: 16,
                fill: '#888',
                selectable: false,
                evented: false
            });

            canvas.add(text);
            window.pageNumberObjects.push({ canvas, text });
        });

        window.pageNumbersVisible = true;
        btn.innerHTML = '<span class="mr-2">❌</span>Remove Page Numbers';
    } else {
        // Remove page numbers
        window.pageNumberObjects.forEach(({ canvas, text }) => {
            canvas.remove(text);
        });

        window.pageNumbersVisible = false;
        window.pageNumberObjects = [];
        btn.innerHTML = '<span class="mr-2">🔢</span>Add Page Numbers';
    }

    window.fabricCanvases.forEach(canvas => canvas.renderAll());
};

window.addTextbox = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const textbox = new fabric.Textbox('Textbox', {
        left: 120, top: 120, width: 180, fontSize: 18, fill: '#333', backgroundColor: '#f3f4f6'
    });
    canvas.add(textbox);
    canvas.setActiveObject(textbox);
};

window.addEllipse = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const ellipse = new fabric.Ellipse({
        left: 200, top: 200, rx: 60, ry: 30, fill: 'rgba(0,0,255,0.1)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(ellipse);
    canvas.setActiveObject(ellipse);
};

window.addPolygon = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const points = [
        { x: 50, y: 0 }, { x: 100, y: 40 }, { x: 80, y: 100 },
        { x: 20, y: 100 }, { x: 0, y: 40 }
    ];
    const poly = new fabric.Polygon(points, {
        left: 250, top: 200, fill: 'rgba(255,0,0,0.1)', stroke: '#333', strokeWidth: 2
    });
    canvas.add(poly);
    canvas.setActiveObject(poly);
};

window.addArrow = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const arrow = new fabric.Polyline([
        { x: 0, y: 10 }, { x: 60, y: 10 }, { x: 50, y: 0 }, { x: 60, y: 10 }, { x: 50, y: 20 }
    ], {
        left: 300, top: 300, fill: '', stroke: '#333', strokeWidth: 3
    });
    canvas.add(arrow);
    canvas.setActiveObject(arrow);
};

window.addHighlight = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const obj = canvas.getActiveObject();
    if (obj && obj.type === 'i-text') {
        obj.set('backgroundColor', '#fff59d');
        canvas.renderAll();
    }
};

window.addCallout = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    // Simple callout: ellipse + line + text
    const ellipse = new fabric.Ellipse({
        left: 300, top: 100, rx: 60, ry: 30, fill: '#e0f7fa', stroke: '#0288d1', strokeWidth: 2
    });
    const line = new fabric.Line([360, 130, 400, 180], { stroke: "#0288d1", strokeWidth: 2 });
    const text = new fabric.IText('Callout', { left: 410, top: 180, fontSize: 16, fill: '#0288d1' });
    const group = new fabric.Group([ellipse, line, text]);
    canvas.add(group);
    canvas.setActiveObject(group);
};

window.addRuler = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    // Simple horizontal ruler
    const line = new fabric.Line([100, 100, 400, 100], { stroke: "#888", strokeWidth: 2 });
    canvas.add(line);
    canvas.setActiveObject(line);
};
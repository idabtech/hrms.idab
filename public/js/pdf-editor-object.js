// Object tab actions: bringForward, sendBackward, toggleLock, changeFillColor, changeStrokeColor, changeStrokeWidth, changeOpacity, mirrorObject, lockAllObjects, unlockAllObjects, pickColor, duplicateStyle, pasteStyle, alignObjects, distributeObjects, nudgeObject

window.bringForward = function() {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj) {
        window.activeCanvas.bringForward(obj);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.sendBackward = function() {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj) {
        window.activeCanvas.sendBackwards(obj);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.toggleLock = function() {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj) {
        obj.selectable = !obj.selectable;
        obj.evented = obj.selectable;
        window.activeCanvas.discardActiveObject();
        window.activeCanvas.renderAll();
    }
};

window.changeFillColor = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (window.showModal) {
        window.showModal({
            message: 'Change Fill Color',
            inputs: [{ id: 'fill', label: 'Fill Color', type: 'color', value: '#ff0000' }],
            onOk: ({ fill }) => {
                if (obj) {
                    if (obj.type === "i-text") {
                        obj.set("backgroundColor", fill);
                    } else if (obj.set) {
                        obj.set("fill", fill);
                    }
                    window.activeCanvas.renderAll();
                    window.saveCanvasState(window.activeCanvas);
                }
            }
        });
    }
};

window.changeStrokeColor = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (window.showModal) {
        window.showModal({
            message: 'Change Stroke Color',
            inputs: [{ id: 'stroke', label: 'Stroke Color', type: 'color', value: '#0000ff' }],
            onOk: ({ stroke }) => {
                if (obj && obj.set) {
                    obj.set("stroke", stroke);
                    window.activeCanvas.renderAll();
                    window.saveCanvasState(window.activeCanvas);
                }
            }
        });
    }
};

window.changeStrokeWidth = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (window.showModal) {
        window.showModal({
            message: 'Change Stroke Width',
            inputs: [{ id: 'width', label: 'Stroke Width', type: 'number', value: 2 }],
            onOk: ({ width }) => {
                const w = parseInt(width);
                if (obj && obj.set && !isNaN(w)) {
                    obj.set("strokeWidth", w);
                    window.activeCanvas.renderAll();
                    window.saveCanvasState(window.activeCanvas);
                }
            }
        });
    }
};

window.changeOpacity = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (window.showModal) {
        window.showModal({
            message: 'Change Opacity',
            inputs: [{ id: 'opacity', label: 'Opacity (0-1)', type: 'number', value: obj.opacity || 1 }],
            onOk: ({ opacity }) => {
                const op = parseFloat(opacity);
                if (obj && !isNaN(op)) {
                    obj.set("opacity", op);
                    window.activeCanvas.renderAll();
                    window.saveCanvasState(window.activeCanvas);
                }
            }
        });
    }
};

window.mirrorObject = function () {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (obj) {
        obj.set('flipX', !obj.flipX);
        window.activeCanvas.renderAll();
    }
};

window.lockAllObjects = function () {
    if (!window.activeCanvas) return;
    window.activeCanvas.getObjects().forEach(obj => obj.selectable = false);
    window.activeCanvas.discardActiveObject();
    window.activeCanvas.renderAll();
};

window.unlockAllObjects = function () {
    if (!window.activeCanvas) return;
    window.activeCanvas.getObjects().forEach(obj => obj.selectable = true);
    window.activeCanvas.renderAll();
};

window.pickColor = function() {
    if (window.showInfo) {
        window.showInfo('Color picker not implemented. Use Fill/Stroke Color tools.');
    } else {
        alert('Color picker not implemented. Use Fill/Stroke Color tools.');
    }
};

window.duplicateStyle = function() {
    if (window.showInfo) {
        window.showInfo('Copy style not implemented.');
    } else {
        alert('Copy style not implemented.');
    }
};

window.pasteStyle = function() {
    if (window.showInfo) {
        window.showInfo('Paste style not implemented.');
    } else {
        alert('Paste style not implemented.');
    }
};

window.alignObjects = function(/*align*/) {
    if (window.showInfo) {
        window.showInfo('alignObjects not implemented.');
    } else {
        alert('alignObjects not implemented.');
    }
};

window.distributeObjects = function(/*dir*/) {
    if (window.showInfo) {
        window.showInfo('distributeObjects not implemented.');
    } else {
        alert('distributeObjects not implemented.');
    }
};

window.nudgeObject = function(/*dir*/) {
    if (window.showInfo) {
        window.showInfo('nudgeObject not implemented.');
    } else {
        alert('nudgeObject not implemented.');
    }
};

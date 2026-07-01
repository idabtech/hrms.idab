// Image tab actions: applyGrayscale, applyInvert, removeImageFilters, flipImageX, flipImageY, rotateObject, cropImage, resizeObject, replaceImage

window.applyGrayscale = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (obj && obj.type === "image") {
        obj.filters = [new fabric.Image.filters.Grayscale()];
        obj.applyFilters();
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.applyInvert = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (obj && obj.type === "image") {
        obj.filters = [new fabric.Image.filters.Invert()];
        obj.applyFilters();
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.removeImageFilters = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (obj && obj.type === "image") {
        obj.filters = [];
        obj.applyFilters();
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.flipImageX = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (obj && obj.type === "image") {
        obj.set("flipX", !obj.flipX);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.flipImageY = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (obj && obj.type === "image") {
        obj.set("flipY", !obj.flipY);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.rotateObject = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (window.showModal) {
        window.showModal({
            message: 'Rotate Object',
            inputs: [{ id: 'angle', label: 'Angle (degrees)', type: 'number', value: obj.angle || 0 }],
            onOk: ({ angle }) => {
                const a = parseInt(angle);
                if (obj && !isNaN(a)) {
                    obj.set("angle", a);
                    window.activeCanvas.renderAll();
                    window.saveCanvasState(window.activeCanvas);
                }
            }
        });
    }
};

window.cropImage = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (obj && obj.type === "image") {
        const left = obj.left || 0, top = obj.top || 0;
        const cropSize = Math.min(obj.width * obj.scaleX, obj.height * obj.scaleY, 100);
        obj.set({
            cropX: 0,
            cropY: 0,
            width: cropSize,
            height: cropSize,
            scaleX: 1,
            scaleY: 1,
            left: left,
            top: top
        });
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.resizeObject = function() {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (window.showModal) {
        window.showModal({
            message: 'Resize Object',
            inputs: [{ id: 'scale', label: 'Scale (e.g. 1, 0.5)', type: 'number', value: obj.scaleX || 1 }],
            onOk: ({ scale }) => {
                const s = parseFloat(scale);
                if (obj && !isNaN(s)) {
                    obj.scaleX = s;
                    obj.scaleY = s;
                    window.activeCanvas.renderAll();
                    window.saveCanvasState(window.activeCanvas);
                }
            }
        });
    }
};

window.replaceImage = function () {
    if (!window.activeCanvas) return;
    const obj = window.activeCanvas.getActiveObject();
    if (obj && obj.type === "image") {
        if (window.showModal) {
            window.showModal({
                message: 'Select new image:',
                inputs: [{ id: 'imgfile', label: 'Image', type: 'file' }],
                onOk: ({ imgfile }) => {
                    // Not supported in modal, fallback to prompt
                    if (window.showInfo) {
                        window.showInfo('Please use the Insert > Image tool to add a new image, then delete the old one.');
                    } else {
                        alert('Please use the Insert > Image tool to add a new image, then delete the old one.');
                    }
                }
            });
        }
    }
};

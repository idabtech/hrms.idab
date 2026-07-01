// View tab actions: zoomCanvas, toggleRuler, toggleGrid

window.zoomCanvas = function(dir) {
    if (!window.activeCanvas) return;
    let zoom = window.activeCanvas.getZoom();
    if (dir === 'in') zoom *= 1.1;
    else if (dir === 'out') zoom /= 1.1;
    zoom = Math.max(0.2, Math.min(zoom, 5));
    window.activeCanvas.setZoom(zoom);
    window.activeCanvas.requestRenderAll();
};

window.toggleRuler = function () {
    if (!window.activeCanvas) return;
    let ruler = document.getElementById('pdf-ruler');
    if (ruler) {
        ruler.remove();
        return;
    }
    ruler = document.createElement('div');
    ruler.id = 'pdf-ruler';
    ruler.style.position = 'absolute';
    ruler.style.left = '0';
    ruler.style.top = '0';
    ruler.style.width = '100%';
    ruler.style.height = '24px';
    ruler.style.background = 'repeating-linear-gradient(to right, #bbb, #bbb 1px, transparent 1px, transparent 20px)';
    ruler.style.pointerEvents = 'none';
    ruler.style.zIndex = 1000;
    document.querySelector('#pdf-editor-container').prepend(ruler);
};

window.toggleGrid = function () {
    if (!window.activeCanvas) return;
    let grid = document.getElementById('pdf-grid');
    if (grid) {
        grid.remove();
        return;
    }
    grid = document.createElement('div');
    grid.id = 'pdf-grid';
    grid.style.position = 'absolute';
    grid.style.left = '0';
    grid.style.top = '24px';
    grid.style.width = '100%';
    grid.style.height = 'calc(100% - 24px)';
    grid.style.background = 'repeating-linear-gradient(#eee 0 1px, transparent 1px 20px), repeating-linear-gradient(90deg, #eee 0 1px, transparent 1px 20px)';
    grid.style.pointerEvents = 'none';
    grid.style.zIndex = 999;
    document.querySelector('#pdf-editor-container').prepend(grid);
};

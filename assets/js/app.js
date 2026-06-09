(function () {
    const canvas = document.getElementById('stressChart');
    if (!canvas) return;
    const values = JSON.parse(canvas.dataset.values || '[6,5,7,4,5]').map(Number);
    const ctx = canvas.getContext('2d');
    const width = canvas.width = canvas.clientWidth * devicePixelRatio;
    const height = canvas.height = 240 * devicePixelRatio;
    ctx.scale(devicePixelRatio, devicePixelRatio);
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.clientWidth, 240);
    ctx.strokeStyle = '#c7c7c7';
    ctx.lineWidth = 1;
    for (let i = 1; i <= 4; i++) {
        const y = i * 48;
        ctx.beginPath();
        ctx.moveTo(36, y);
        ctx.lineTo(canvas.clientWidth - 20, y);
        ctx.stroke();
    }
    const max = 10;
    const min = 1;
    const step = (canvas.clientWidth - 70) / Math.max(values.length - 1, 1);
    const point = (value, index) => [36 + index * step, 220 - ((value - min) / (max - min)) * 190];
    ctx.strokeStyle = '#8b5c24';
    ctx.lineWidth = 4;
    ctx.beginPath();
    values.forEach((value, index) => {
        const [x, y] = point(value, index);
        if (index === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    });
    ctx.stroke();
    ctx.fillStyle = '#1c2b1c';
    values.forEach((value, index) => {
        const [x, y] = point(value, index);
        ctx.beginPath();
        ctx.arc(x, y, 5, 0, Math.PI * 2);
        ctx.fill();
    });
})();

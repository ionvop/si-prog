function initialize() {
    document.querySelector("body > *").style.outline = "0.1rem solid #0f0";
    document.querySelector("body > *").style.aspectRatio = "9 / 16";
    document.querySelector("body > *").style.transformOrigin = "top";
    document.querySelector("body > *").style.overflow = "hidden";
    adjustScale();
}

function adjustScale() {
    let scale = Math.min(window.innerHeight / document.querySelector("body > *").clientHeight, 1);
    scale *= 90;
    scale = scale + "%";
    document.querySelector("body > *").style.transform = `scale(${scale}) translateY(1rem)`;
}

initialize();

window.addEventListener("resize", () => {
    adjustScale();
});
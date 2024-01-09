

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

function getRequest(targetUrl, queryParams) {
    // Create a URL object with the target URL
    let url = new URL(targetUrl, location.href);

    // Append query parameters to the URL
    for (let key in queryParams) {
        if (queryParams.hasOwnProperty(key)) {
            url.searchParams.set(key, queryParams[key]);
        }
    }

    // Navigate to the generated URL
    location.href = url.toString();
}

function escapeHtml(text) {
    let div = document.createElement("div");
    div.appendChild(document.createTextNode(text.replace("/\n/g", "<br>")));
    return div.innerHTML;
}

function convertNewlinesToBr(input) {
    // Replace existing <br> tags with a placeholder
    const placeholder = '__BR_TAG_PLACEHOLDER__';
    const escapedInput = input.replace(/<br\s*\/?>/gi, placeholder);

    // Replace newline characters with <br>
    const result = escapedInput.replace(/\n/g, '<br>');

    // Restore the original <br> tags
    return result.replace(new RegExp(placeholder, 'g'), '<br>');
}

function breakpoint(input) {
    document.head.innerHTML = "";
    let display = document.createElement("pre");

    try {
        let data = JSON.parse(input)
        display.innerText = JSON.stringify(data, null, 4);
    } catch (error) {
        display.innerText = input;
    }

    document.body.appendChild(display);
}

initialize();

window.addEventListener("resize", () => {
    adjustScale();
});
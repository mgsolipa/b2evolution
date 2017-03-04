Prism.hooks.add('complete', function (env) {
    if (!env.code) {
        return;
    }

    // works only for <code> wrapped inside <pre> (not inline)
    var pre = env.element.parentNode;
    var clsReg = /\s*\bvalidhtml-linenrbs\b\s*/;
    if (
        !pre || !/pre/i.test(pre.nodeName) ||
            // Abort only if nor the <pre> nor the <code> have the class
        (!clsReg.test(pre.className) && !clsReg.test(env.element.className))
    ) {
        return;
    }

    if (env.element.querySelector(".line-numbers-rows")) {
        // Abort if line numbers already exists
        return;
    }

    if (clsReg.test(env.element.className)) {
        // Remove the class "line-numbers" from the <code>
        env.element.className = env.element.className.replace(clsReg, '');
    }
    if (!clsReg.test(pre.className)) {
        // Add the class "line-numbers" to the <pre>
        pre.className += ' validhtml-linenrbs';
    }

    var match = env.code.match(/\n(?!$)/g);
    var linesNum = match ? match.length + 1 : 1;
    var lineNumbersWrapper;

    var lines = new Array(linesNum + 1);
    lines = lines.join('<span></span>');

    lineNumbersWrapper = document.createElement('span');
    lineNumbersWrapper.className = 'line-numbers-rows';
    lineNumbersWrapper.innerHTML = lines;

    if (pre.hasAttribute('title')) {
        pre.style.counterReset = 'linenumber ' + (parseInt(pre.getAttribute('title'), 10) - 1);
    }

    env.element.appendChild(lineNumbersWrapper);

});
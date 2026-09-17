// ============================================================
// REDSIM LANDING ANIMATIONS
// Splash | TextType | BorderGlow | CardSwap | KnowledgeBase | LightRays
// ============================================================

// ===== SPLASH SCREEN =====
(function() {
  window.initSplash = function(onComplete) {
    var splash = document.getElementById('splash-screen');
    if (!splash) return;

    if (sessionStorage.getItem('redsim_splash_shown')) {
      splash.remove();
      if (onComplete) onComplete();
      return;
    }

    var text = 'REDSIM';
    var container = splash.querySelector('.splash-text');
    if (!container) return;

    text.split('').forEach(function(char, i) {
      var span = document.createElement('span');
      span.textContent = char;
      span.className = 'splash-letter';
      span.style.animationDelay = (i * 0.15) + 's';
      container.appendChild(span);
    });

    setTimeout(function() {
      splash.classList.add('splash-exit');
      setTimeout(function() {
        splash.remove();
        sessionStorage.setItem('redsim_splash_shown', '1');
        if (onComplete) onComplete();
      }, 800);
    }, text.length * 150 + 1200);
  };
})();

// ===== TEXT TYPE ANIMATION =====
(function() {
  window.initTextType = function(element, sentences, opts) {
    opts = Object.assign({
      typingSpeed: 50,
      deletingSpeed: 30,
      pauseDuration: 2000,
      loop: true,
      cursorChar: '|'
    }, opts);

    if (!element) return;

    var contentEl = document.createElement('span');
    contentEl.className = 'texttype-content';
    var cursorEl = document.createElement('span');
    cursorEl.className = 'texttype-cursor';
    cursorEl.textContent = opts.cursorChar;
    element.appendChild(contentEl);
    element.appendChild(cursorEl);

    var sentenceIdx = 0, charIdx = 0, isDeleting = false, displayedText = '';

    function tick() {
      var current = sentences[sentenceIdx];
      if (isDeleting) {
        if (displayedText.length > 0) {
          displayedText = displayedText.slice(0, -1);
          contentEl.textContent = displayedText;
          setTimeout(tick, opts.deletingSpeed);
        } else {
          isDeleting = false;
          sentenceIdx = (sentenceIdx + 1) % sentences.length;
          charIdx = 0;
          if (!opts.loop && sentenceIdx === 0) return;
          setTimeout(tick, opts.pauseDuration / 2);
        }
      } else {
        if (charIdx < current.length) {
          displayedText += current[charIdx];
          charIdx++;
          contentEl.textContent = displayedText;
          setTimeout(tick, opts.typingSpeed);
        } else {
          if (!opts.loop && sentenceIdx === sentences.length - 1) return;
          setTimeout(function() {
            isDeleting = true;
            tick();
          }, opts.pauseDuration);
        }
      }
    }
    setTimeout(tick, 500);
  };
})();

// ===== BORDER GLOW (vanilla JS) =====
(function() {
  function getCenter(el) {
    var r = el.getBoundingClientRect();
    return [r.width / 2, r.height / 2];
  }

  function getEdgeProximity(el, x, y) {
    var c = getCenter(el);
    var cx = c[0], cy = c[1];
    var dx = x - cx, dy = y - cy;
    var kx = dx !== 0 ? cx / Math.abs(dx) : Infinity;
    var ky = dy !== 0 ? cy / Math.abs(dy) : Infinity;
    return Math.min(Math.max(1 / Math.min(kx, ky), 0), 1);
  }

  function getCursorAngle(el, x, y) {
    var c = getCenter(el);
    var dx = x - c[0], dy = y - c[1];
    if (dx === 0 && dy === 0) return 0;
    var deg = Math.atan2(dy, dx) * (180 / Math.PI) + 90;
    if (deg < 0) deg += 360;
    return deg;
  }

  window.initBorderGlow = function(selector) {
    var els = document.querySelectorAll(selector);
    els.forEach(function(el) {
      el.addEventListener('pointermove', function(e) {
        var rect = el.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        var edge = getEdgeProximity(el, x, y);
        var angle = getCursorAngle(el, x, y);
        el.style.setProperty('--edge-proximity', (edge * 100).toFixed(3));
        el.style.setProperty('--cursor-angle', angle.toFixed(3) + 'deg');
      });
      el.addEventListener('pointerleave', function() {
        el.style.setProperty('--edge-proximity', '0');
      });
    });
  };
})();

// ===== CARD SWAP (vanilla JS + GSAP) =====
(function() {
  function makeSlot(i, distX, distY, total) {
    return {
      x: i * distX,
      y: -i * distY,
      z: -i * distX * 1.5,
      zIndex: total - i
    };
  }

  window.initCardSwap = function(container, opts) {
    opts = Object.assign({
      cardDistance: 60,
      verticalDistance: 70,
      delay: 5000,
      skewAmount: 6,
    }, opts || {});

    var cards = Array.from(container.querySelectorAll('.cs-card'));
    var total = cards.length;
    var order = cards.map(function(_, i) { return i; });

    cards.forEach(function(card, i) {
      var slot = makeSlot(i, opts.cardDistance, opts.verticalDistance, total);
      gsap.set(card, {
        x: slot.x, y: slot.y, z: slot.z,
        xPercent: -50, yPercent: -50,
        skewY: opts.skewAmount,
        transformOrigin: 'center center',
        zIndex: slot.zIndex,
        force3D: true
      });
    });

    var cfg = {
      ease: 'elastic.out(0.6,0.9)',
      durDrop: 2,
      durMove: 2,
      durReturn: 2,
      promoteOverlap: 0.9,
      returnDelay: 0.05
    };

    function swap() {
      if (order.length < 2) return;
      var front = order[0];
      var rest = order.slice(1);
      var elFront = cards[front];
      var tl = gsap.timeline();

      tl.to(elFront, { y: '+=500', duration: cfg.durDrop, ease: cfg.ease });
      tl.addLabel('promote', '-=' + (cfg.durDrop * cfg.promoteOverlap));
      rest.forEach(function(idx, i) {
        var el = cards[idx];
        var slot = makeSlot(i, opts.cardDistance, opts.verticalDistance, total);
        tl.set(el, { zIndex: slot.zIndex }, 'promote');
        tl.to(el, {
          x: slot.x, y: slot.y, z: slot.z,
          duration: cfg.durMove, ease: cfg.ease
        }, 'promote+=' + (i * 0.15));
      });

      var backSlot = makeSlot(total - 1, opts.cardDistance, opts.verticalDistance, total);
      tl.addLabel('return', 'promote+=' + (cfg.durMove * cfg.returnDelay));
      tl.call(function() { gsap.set(elFront, { zIndex: backSlot.zIndex }); }, undefined, 'return');
      tl.to(elFront, {
        x: backSlot.x, y: backSlot.y, z: backSlot.z,
        duration: cfg.durReturn, ease: cfg.ease
      }, 'return');
      tl.call(function() { order.shift(); order.push(front); });
    }

    swap();
    setInterval(swap, opts.delay);
  };
})();

// ===== KNOWLEDGE BASE ANIMATION =====
(function() {
  window.initKBAnimation = function() {
    var cards = document.querySelectorAll('.kb-card');
    if (!cards.length) return;

    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          var el = entry.target;
          var delay = parseFloat(el.dataset.delay || 0);
          setTimeout(function() { el.classList.add('kb-visible'); }, delay);
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.1 });

    cards.forEach(function(card) { observer.observe(card); });
  };
})();

// ===== LIGHT RAYS BACKGROUND (Pure Vanilla WebGL) =====
(function() {
  window.initLightRays = function(container, opts) {
    if (!container) return;
    opts = Object.assign({
      raysOrigin: 'top-center',
      raysColor: [0.2, 0.7, 1.0],
      raysSpeed: 0.8,
      lightSpread: 0.45,
      rayLength: 2.5,
      followMouse: true,
      mouseInfluence: 0.12,
      noiseAmount: 0,
      distortion: 0,
      pulsating: false,
      fadeDistance: 1.2,
      saturation: 0.9,
    }, opts || {});

    var canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;display:block;';
    while (container.firstChild) container.removeChild(container.firstChild);
    container.appendChild(canvas);

    var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    if (!gl) return;

    var mouseRef = { x: 0.5, y: 0.5 };
    var smoothMouse = { x: 0.5, y: 0.5 };

    function getAnchorAndDir(origin, w, h) {
      var outside = 0.2;
      switch (origin) {
        case 'top-left':      return { anchor: [0, -outside * h], dir: [0, 1] };
        case 'top-right':     return { anchor: [w, -outside * h], dir: [0, 1] };
        case 'left':          return { anchor: [-outside * w, 0.5 * h], dir: [1, 0] };
        case 'right':         return { anchor: [(1 + outside) * w, 0.5 * h], dir: [-1, 0] };
        case 'bottom-center': return { anchor: [0.5 * w, (1 + outside) * h], dir: [0, -1] };
        default:              return { anchor: [0.5 * w, -outside * h], dir: [0, 1] };
      }
    }

    var vertSrc = 'attribute vec2 p;void main(){gl_Position=vec4(p,0.0,1.0);}';

    var fragSrc = [
      'precision highp float;',
      'uniform float iTime;',
      'uniform vec2 iRes;',
      'uniform vec2 rayPos;',
      'uniform vec2 rayDir;',
      'uniform vec3 raysColor;',
      'uniform float raysSpeed,lightSpread,rayLength,pulsating,fadeDistance,saturation,mouseInfluence,distortion;',
      'uniform vec2 mousePos;',
      'float RS(vec2 src,vec2 dir,vec2 c,float sA,float sB,float sp){',
      '  vec2 d=c-src;float len=length(d);if(len<0.001)return 0.0;',
      '  float ca=dot(d/len,dir);',
      '  float da=ca+distortion*sin(iTime*2.0+len*0.01)*0.2;',
      '  float sf=pow(max(da,0.0),1.0/max(lightSpread,0.001));',
      '  float mD=iRes.x*rayLength;',
      '  float lF=clamp((mD-len)/mD,0.0,1.0);',
      '  float fF=clamp((iRes.x*fadeDistance-len)/(iRes.x*fadeDistance),0.5,1.0);',
      '  float pu=pulsating>0.5?(0.8+0.2*sin(iTime*sp*3.0)):1.0;',
      '  float b=clamp((0.45+0.15*sin(da*sA+iTime*sp))+(0.3+0.2*cos(-da*sB+iTime*sp)),0.0,1.0);',
      '  return b*lF*fF*sf*pu;',
      '}',
      'void main(){',
      '  vec2 co=vec2(gl_FragCoord.x,iRes.y-gl_FragCoord.y);',
      '  vec2 fd=rayDir;',
      '  if(mouseInfluence>0.0){vec2 ms=mousePos*iRes;fd=normalize(mix(rayDir,normalize(ms-rayPos),mouseInfluence));}',
      '  float r1=RS(rayPos,fd,co,36.2214,21.11349,1.5*raysSpeed);',
      '  float r2=RS(rayPos,fd,co,22.3991,18.0234,1.1*raysSpeed);',
      '  vec4 col=vec4(1.0)*(r1*0.5+r2*0.4);',
      '  float br=1.0-(co.y/iRes.y);',
      '  col.r*=0.1+br*0.8;col.g*=0.3+br*0.6;col.b*=0.5+br*0.5;',
      '  if(saturation!=1.0){float g=dot(col.rgb,vec3(0.299,0.587,0.114));col.rgb=mix(vec3(g),col.rgb,saturation);}',
      '  col.rgb*=raysColor;',
      '  gl_FragColor=col;',
      '}'
    ].join('\n');

    function mkSh(type, src) {
      var sh = gl.createShader(type);
      gl.shaderSource(sh, src); gl.compileShader(sh); return sh;
    }
    var prog = gl.createProgram();
    gl.attachShader(prog, mkSh(gl.VERTEX_SHADER, vertSrc));
    gl.attachShader(prog, mkSh(gl.FRAGMENT_SHADER, fragSrc));
    gl.linkProgram(prog); gl.useProgram(prog);

    var buf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1,3,-1,-1,3]), gl.STATIC_DRAW);
    var loc = gl.getAttribLocation(prog, 'p');
    gl.enableVertexAttribArray(loc);
    gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

    var U = {};
    ['iTime','iRes','rayPos','rayDir','raysColor','raysSpeed','lightSpread','rayLength',
     'pulsating','fadeDistance','saturation','mousePos','mouseInfluence','distortion'
    ].forEach(function(n) { U[n] = gl.getUniformLocation(prog, n); });

    gl.uniform3fv(U.raysColor,   new Float32Array(opts.raysColor));
    gl.uniform1f(U.raysSpeed,    opts.raysSpeed);
    gl.uniform1f(U.lightSpread,  opts.lightSpread);
    gl.uniform1f(U.rayLength,    opts.rayLength);
    gl.uniform1f(U.pulsating,    opts.pulsating ? 1.0 : 0.0);
    gl.uniform1f(U.fadeDistance, opts.fadeDistance);
    gl.uniform1f(U.saturation,   opts.saturation);
    gl.uniform1f(U.mouseInfluence, opts.mouseInfluence);
    gl.uniform1f(U.distortion,   opts.distortion);

    var cW = 0, cH = 0;
    function resize() {
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      var w = Math.round(container.clientWidth * dpr);
      var h = Math.round(container.clientHeight * dpr);
      if (w === cW && h === cH) return;
      cW = w; cH = h;
      canvas.width = w; canvas.height = h;
      gl.viewport(0, 0, w, h);
      gl.uniform2f(U.iRes, w, h);
      var ad = getAnchorAndDir(opts.raysOrigin, w, h);
      gl.uniform2fv(U.rayPos, new Float32Array(ad.anchor));
      gl.uniform2fv(U.rayDir, new Float32Array(ad.dir));
    }

    window.addEventListener('resize', resize);
    resize();

    if (opts.followMouse) {
      window.addEventListener('mousemove', function(e) {
        var rect = container.getBoundingClientRect();
        mouseRef.x = (e.clientX - rect.left) / rect.width;
        mouseRef.y = (e.clientY - rect.top) / rect.height;
      });
    }

    gl.enable(gl.BLEND);
    gl.blendFunc(gl.ONE, gl.ONE);

    function loop(t) {
      resize();
      gl.uniform1f(U.iTime, t * 0.001);
      if (opts.followMouse) {
        var sm = 0.93;
        smoothMouse.x = smoothMouse.x * sm + mouseRef.x * (1 - sm);
        smoothMouse.y = smoothMouse.y * sm + mouseRef.y * (1 - sm);
        gl.uniform2f(U.mousePos, smoothMouse.x, smoothMouse.y);
      }
      gl.clearColor(0, 0, 0, 0);
      gl.clear(gl.COLOR_BUFFER_BIT);
      gl.drawArrays(gl.TRIANGLES, 0, 3);
      requestAnimationFrame(loop);
    }
    requestAnimationFrame(loop);
  };
})();

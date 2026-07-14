// Lightfall WebGL Background - Vanilla JS (converted from React)
(function() {
  const vertex = `
attribute vec2 position;
varying vec2 vUv;
void main() {
  vUv = position * 0.5 + 0.5;
  gl_Position = vec4(position, 0.0, 1.0);
}`;

  const fragment = `
precision highp float;
uniform vec3 iResolution;
uniform vec2 iMouse;
uniform float iTime;
uniform vec3 uColor0, uColor1, uColor2;
uniform vec3 uBgColor;
uniform float uSpeed, uGlow, uDensity, uTwinkle, uZoom, uBgGlow, uOpacity;
uniform float uStreakWidth, uStreakLength;
uniform int uStreakCount;
varying vec2 vUv;

vec3 palette(float h) {
  int idx = int(floor(clamp(h, 0.0, 0.999) * 3.0));
  if (idx <= 0) return uColor0;
  if (idx == 1) return uColor1;
  return uColor2;
}
vec3 tanhv(vec3 x) {
  vec3 e = exp(-2.0 * x);
  return (1.0 - e) / (1.0 + e);
}
vec2 sceneC(vec2 frag, vec2 r) {
  vec2 P = (frag + frag - r) / r.x;
  float z = 0.0, d = 1e3;
  vec4 O = vec4(0.0);
  for (int k = 0; k < 39; k++) {
    if (d <= 1e-4) break;
    O = z * normalize(vec4(P, uZoom, 0.0)) - vec4(0.0, 4.0, 1.0, 0.0) / 4.5;
    d = 1.0 - sqrt(length(O * O));
    z += d;
  }
  return vec2(O.x, atan(O.z, O.y));
}
void main() {
  vec2 r = iResolution.xy;
  vec2 C0 = vUv * r;
  vec2 uv0 = (C0 + C0 - r) / r.x;
  float T = 0.1 * iTime * uSpeed + 9.0;
  float angRings = max(1.0, floor(6.28318 * max(uDensity, 0.05) + 0.5));
  vec2 Y = vec2(5e-3, 6.28318 / angRings);
  vec2 c0 = sceneC(C0, r);
  vec2 cdx = sceneC(C0 + vec2(1.0, 0.0), r);
  vec2 cdy = sceneC(C0 + vec2(0.0, 1.0), r);
  vec2 dCx = cdx - c0; vec2 dCy = cdy - c0;
  dCx.y -= 6.28318 * floor(dCx.y / 6.28318 + 0.5);
  dCy.y -= 6.28318 * floor(dCy.y / 6.28318 + 0.5);
  vec2 fw = abs(dCx) + abs(dCy);
  vec2 C = c0;
  vec2 P = 2.0 * uv0 - (r / r.x) * vec2(0.0, 1.0);
  vec4 O = vec4(uBgColor * 90.0 * uBgGlow / (1e3 * dot(P, P) + 6.0), 0.0);
  float zr = 5e-4 * uStreakWidth;
  vec2 rr = vec2(max(length(fw), 1e-5));
  float tail = 19.0 / max(uStreakLength, 0.05);
  for (int m = 0; m < 16; m++) {
    if (m >= uStreakCount) break;
    float jf = float(m) + 1.0;
    float ic = fract(sin(dot(vec2(jf, floor(C.x / Y.x + 0.5)), vec2(7.0, 11.0)) * 73.0));
    vec2 Pp = C - (T + T * ic) * vec2(0.0, 1.0);
    Pp -= floor(Pp / Y + 0.5) * Y;
    float h = fract(8663.0 * ic);
    vec3 col = palette(h);
    float weight = mix(1.5, 1.0 + sin(T + 7.0 * h + 4.0), uTwinkle);
    vec2 inner = vec2(length(max(Pp, vec2(-1.0, 0.0))), length(Pp) - zr) - zr;
    vec2 sm = vec2(1.0) - smoothstep(-rr, rr, inner);
    O.rgb += dot(sm, vec2(exp(tail * Pp.y), 3.0)) * col * weight;
    C.x += Y.x / 8.0;
  }
  vec3 colr = sqrt(tanhv(max(O.rgb * uGlow - vec3(0.04, 0.08, 0.02), 0.0)));
  gl_FragColor = vec4(colr, uOpacity);
}`;

  function hexToRGB(hex) {
    const c = hex.replace('#', '').padEnd(6, '0');
    return [parseInt(c.slice(0,2),16)/255, parseInt(c.slice(2,4),16)/255, parseInt(c.slice(4,6),16)/255];
  }

  window.initLightfall = function(container, opts) {
    opts = Object.assign({
      colors: ['#A6C8FF','#5227FF','#FF9FFC'],
      backgroundColor: '#0A29FF',
      speed: 0.5, streakCount: 2, streakWidth: 1, streakLength: 1,
      glow: 1, density: 0.6, twinkle: 1, zoom: 3,
      backgroundGlow: 0.5, opacity: 1
    }, opts);

    const canvas = document.createElement('canvas');
    canvas.style.cssText = 'width:100%;height:100%;display:block;';
    container.appendChild(canvas);
    const gl = canvas.getContext('webgl', { alpha: true, antialias: true });
    if (!gl) return;

    function createShader(type, src) {
      const s = gl.createShader(type);
      gl.shaderSource(s, src);
      gl.compileShader(s);
      return s;
    }
    const prog = gl.createProgram();
    gl.attachShader(prog, createShader(gl.VERTEX_SHADER, vertex));
    gl.attachShader(prog, createShader(gl.FRAGMENT_SHADER, fragment));
    gl.linkProgram(prog);
    gl.useProgram(prog);

    const buf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1, 3,-1, -1,3]), gl.STATIC_DRAW);
    const pos = gl.getAttribLocation(prog, 'position');
    gl.enableVertexAttribArray(pos);
    gl.vertexAttribPointer(pos, 2, gl.FLOAT, false, 0, 0);

    const u = (n) => gl.getUniformLocation(prog, n);
    const c = opts.colors.map(hexToRGB);
    const bg = hexToRGB(opts.backgroundColor);

    gl.uniform3fv(u('uColor0'), c[0] || [0.65,0.78,1]);
    gl.uniform3fv(u('uColor1'), c[1] || [0.32,0.15,1]);
    gl.uniform3fv(u('uColor2'), c[2] || [1,0.62,0.99]);
    gl.uniform3fv(u('uBgColor'), bg);
    gl.uniform1f(u('uSpeed'), opts.speed);
    gl.uniform1i(u('uStreakCount'), Math.min(16, opts.streakCount));
    gl.uniform1f(u('uStreakWidth'), opts.streakWidth);
    gl.uniform1f(u('uStreakLength'), opts.streakLength);
    gl.uniform1f(u('uGlow'), opts.glow);
    gl.uniform1f(u('uDensity'), opts.density);
    gl.uniform1f(u('uTwinkle'), opts.twinkle);
    gl.uniform1f(u('uZoom'), opts.zoom);
    gl.uniform1f(u('uBgGlow'), opts.backgroundGlow);
    gl.uniform1f(u('uOpacity'), opts.opacity);
    gl.uniform2fv(u('iMouse'), [0, 0]);

    const uRes = u('iResolution'), uTime = u('iTime');

    function resize() {
      const r = container.getBoundingClientRect();
      const dpr = window.devicePixelRatio || 1;
      canvas.width = r.width * dpr;
      canvas.height = r.height * dpr;
      gl.viewport(0, 0, canvas.width, canvas.height);
      gl.uniform3fv(uRes, [canvas.width, canvas.height, 1]);
    }
    resize();
    window.addEventListener('resize', resize);

    let raf;
    function loop(t) {
      raf = requestAnimationFrame(loop);
      gl.uniform1f(uTime, t * 0.001);
      gl.drawArrays(gl.TRIANGLES, 0, 3);
    }
    raf = requestAnimationFrame(loop);

    container.addEventListener('mousemove', function(e) {
      const r = canvas.getBoundingClientRect();
      const dpr = window.devicePixelRatio || 1;
      gl.uniform2fv(u('iMouse'), [(e.clientX - r.left)*dpr, (r.height - (e.clientY - r.top))*dpr]);
    });

    return function destroy() {
      cancelAnimationFrame(raf);
      window.removeEventListener('resize', resize);
    };
  };
})();

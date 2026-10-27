    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Init Splash Screen
            initSplash(function() {});

            // 2. Init Ghost Fibers Background (WebGL1)
            (function() {
                var canvas = document.getElementById('ghost-fibers-canvas');
                if (!canvas) return;

                // Force WebGL1 — avoids GLSL ES 3.00 attribute/in keyword mismatch
                var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (!gl) { console.warn('GridScan: WebGL not supported'); return; }
                
                var ext = gl.getExtension('OES_standard_derivatives');
                if (!ext) { console.warn('GridScan: OES_standard_derivatives not supported'); }

                // ---- GLSL ES 1.00 Vertex Shader ----
                var vsSource = [
                    'attribute vec2 aPos;',
                    'void main(){gl_Position=vec4(aPos,0.0,1.0);}'
                ].join('\n');

                // ---- GLSL ES 1.00 Fragment Shader ----
                var fsSource = [
                    '#extension GL_OES_standard_derivatives : enable',
                    'precision highp float;',
                    'uniform vec3 iResolution;',
                    'uniform float iTime;',
                    'uniform vec2 uSkew;',
                    'uniform float uTilt;',
                    'uniform float uYaw;',
                    'uniform float uLineThickness;',
                    'uniform vec3 uLinesColor;',
                    'uniform vec3 uScanColor;',
                    'uniform float uGridScale;',
                    'uniform float uLineStyle;',
                    'uniform float uLineJitter;',
                    'uniform float uScanOpacity;',
                    'uniform float uScanDirection;',
                    'uniform float uNoise;',
                    'uniform float uBloomOpacity;',
                    'uniform float uScanGlow;',
                    'uniform float uScanSoftness;',
                    'uniform float uPhaseTaper;',
                    'uniform float uScanDuration;',
                    'uniform float uScanDelay;',
                    'uniform float uLightMode;',
                    'uniform float uScanStarts[8];',
                    'uniform float uScanCount;',
                    'const int MAX_SCANS = 8;',
                    'float smoother01(float a, float b, float x){',
                    '  float t = clamp((x - a) / max(1e-5, (b - a)), 0.0, 1.0);',
                    '  return t * t * t * (t * (t * 6.0 - 15.0) + 10.0);',
                    '}',
                    'void mainImage(out vec4 fragColor, in vec2 fragCoord) {',
                    '    vec2 p = (2.0 * fragCoord - iResolution.xy) / iResolution.y;',
                    '    vec3 ro = vec3(0.0);',
                    '    vec3 rd = normalize(vec3(p, 2.0));',
                    '    float cR = cos(uTilt), sR = sin(uTilt);',
                    '    rd.xy = mat2(cR, -sR, sR, cR) * rd.xy;',
                    '    float cY = cos(uYaw), sY = sin(uYaw);',
                    '    rd.xz = mat2(cY, -sY, sY, cY) * rd.xz;',
                    '    vec2 skew = clamp(uSkew, vec2(-0.7), vec2(0.7));',
                    '    rd.xy += skew * rd.z;',
                    '    vec3 color = vec3(0.0);',
                    '    float minT = 1e20;',
                    '    float gridScale = max(1e-5, uGridScale);',
                    '    float fadeStrength = 2.0;',
                    '    vec2 gridUV = vec2(0.0);',
                    '    float hitIsY = 1.0;',
                    '    for (int i = 0; i < 4; i++) {',
                    '        float isY = float(i < 2);',
                    '        float pos = mix(-0.2, 0.2, float(i)) * isY + mix(-0.5, 0.5, float(i - 2)) * (1.0 - isY);',
                    '        float num = pos - (isY * ro.y + (1.0 - isY) * ro.x);',
                    '        float den = isY * rd.y + (1.0 - isY) * rd.x;',
                    '        float t = num / den;',
                    '        vec3 h = ro + rd * t;',
                    '        float depthBoost = smoothstep(0.0, 3.0, h.z);',
                    '        h.xy += skew * 0.15 * depthBoost;',
                    '        bool use = t > 0.0 && t < minT;',
                    '        gridUV = use ? mix(h.zy, h.xz, isY) / gridScale : gridUV;',
                    '        minT = use ? t : minT;',
                    '        hitIsY = use ? isY : hitIsY;',
                    '    }',
                    '    vec3 hit = ro + rd * minT;',
                    '    float dist = length(hit - ro);',
                    '    float jitterAmt = clamp(uLineJitter, 0.0, 1.0);',
                    '    if (jitterAmt > 0.0) {',
                    '        vec2 j = vec2(sin(gridUV.y * 2.7 + iTime * 1.8), cos(gridUV.x * 2.3 - iTime * 1.6)) * (0.15 * jitterAmt);',
                    '        gridUV += j;',
                    '    }',
                    '    float fx = fract(gridUV.x);',
                    '    float fy = fract(gridUV.y);',
                    '    float ax = min(fx, 1.0 - fx);',
                    '    float ay = min(fy, 1.0 - fy);',
                    '    float wx = fwidth(gridUV.x);',
                    '    float wy = fwidth(gridUV.y);',
                    '    float halfPx = max(0.0, uLineThickness) * 0.5;',
                    '    float tx = halfPx * wx;',
                    '    float ty = halfPx * wy;',
                    '    float aax = wx;',
                    '    float aay = wy;',
                    '    float lineX = 1.0 - smoothstep(tx, tx + aax, ax);',
                    '    float lineY = 1.0 - smoothstep(ty, ty + aay, ay);',
                    '    float primaryMask = max(lineX, lineY);',
                    '    vec2 gridUV2 = (hitIsY > 0.5 ? hit.xz : hit.zy) / gridScale;',
                    '    if (jitterAmt > 0.0) {',
                    '        vec2 j2 = vec2(cos(gridUV2.y * 2.1 - iTime * 1.4), sin(gridUV2.x * 2.5 + iTime * 1.7)) * (0.15 * jitterAmt);',
                    '        gridUV2 += j2;',
                    '    }',
                    '    float fx2 = fract(gridUV2.x);',
                    '    float fy2 = fract(gridUV2.y);',
                    '    float ax2 = min(fx2, 1.0 - fx2);',
                    '    float ay2 = min(fy2, 1.0 - fy2);',
                    '    float wx2 = fwidth(gridUV2.x);',
                    '    float wy2 = fwidth(gridUV2.y);',
                    '    float tx2 = halfPx * wx2;',
                    '    float ty2 = halfPx * wy2;',
                    '    float aax2 = wx2;',
                    '    float aay2 = wy2;',
                    '    float lineX2 = 1.0 - smoothstep(tx2, tx2 + aax2, ax2);',
                    '    float lineY2 = 1.0 - smoothstep(ty2, ty2 + aay2, ay2);',
                    '    float altMask = max(lineX2, lineY2);',
                    '    float edgeDistX = min(abs(hit.x - (-0.5)), abs(hit.x - 0.5));',
                    '    float edgeDistY = min(abs(hit.y - (-0.2)), abs(hit.y - 0.2));',
                    '    float edgeDist = mix(edgeDistY, edgeDistX, hitIsY);',
                    '    float edgeGate = 1.0 - smoothstep(gridScale * 0.5, gridScale * 2.0, edgeDist);',
                    '    altMask *= edgeGate;',
                    '    float lineMask = max(primaryMask, altMask);',
                    '    float fade = exp(-dist * fadeStrength);',
                    '    float dur = max(0.05, uScanDuration);',
                    '    float del = max(0.0, uScanDelay);',
                    '    float scanZMax = 2.0;',
                    '    float widthScale = max(0.1, uScanGlow);',
                    '    float sigma = max(0.001, 0.18 * widthScale * uScanSoftness);',
                    '    float sigmaA = sigma * 2.0;',
                    '    float combinedPulse = 0.0;',
                    '    float combinedAura = 0.0;',
                    '    float cycle = dur + del;',
                    '    float tCycle = mod(iTime, cycle);',
                    '    float scanPhase = clamp((tCycle - del) / dur, 0.0, 1.0);',
                    '    float phase = scanPhase;',
                    '    if (uScanDirection > 0.5 && uScanDirection < 1.5) {',
                    '        phase = 1.0 - phase;',
                    '    } else if (uScanDirection > 1.5) {',
                    '        float t2 = mod(max(0.0, iTime - del), 2.0 * dur);',
                    '        phase = (t2 < dur) ? (t2 / dur) : (1.0 - (t2 - dur) / dur);',
                    '    }',
                    '    float scanZ = phase * scanZMax;',
                    '    float dz = abs(hit.z - scanZ);',
                    '    float lineBand = exp(-0.5 * (dz * dz) / (sigma * sigma));',
                    '    float taper = clamp(uPhaseTaper, 0.0, 0.49);',
                    '    float headW = taper;',
                    '    float tailW = taper;',
                    '    float headFade = smoother01(0.0, headW, phase);',
                    '    float tailFade = 1.0 - smoother01(1.0 - tailW, 1.0, phase);',
                    '    float phaseWindow = headFade * tailFade;',
                    '    float pulseBase = lineBand * phaseWindow;',
                    '    combinedPulse += pulseBase * clamp(uScanOpacity, 0.0, 1.0);',
                    '    float auraBand = exp(-0.5 * (dz * dz) / (sigmaA * sigmaA));',
                    '    combinedAura += (auraBand * 0.25) * phaseWindow * clamp(uScanOpacity, 0.0, 1.0);',
                    '    ',
                    '    float lineVis = lineMask;',
                    '    vec3 gridCol = uLinesColor * lineVis * fade;',
                    '    vec3 scanCol = uScanColor * combinedPulse;',
                    '    vec3 scanAura = uScanColor * combinedAura;',
                    '    color = gridCol + scanCol + scanAura;',
                    '    float n = fract(sin(dot(gl_FragCoord.xy + vec2(iTime * 123.4), vec2(12.9898,78.233))) * 43758.5453123);',
                    '    color += (n - 0.5) * uNoise;',
                    '    color = clamp(color, 0.0, 1.0);',
                    '    float alpha = clamp(max(lineVis, combinedPulse), 0.0, 1.0);',
                    '    float gx = 1.0 - smoothstep(tx * 2.0, tx * 2.0 + aax * 2.0, ax);',
                    '    float gy = 1.0 - smoothstep(ty * 2.0, ty * 2.0 + aay * 2.0, ay);',
                    '    float halo = max(gx, gy) * fade;',
                    '    alpha = max(alpha, halo * clamp(uBloomOpacity, 0.0, 1.0));',
                    '    fragColor = vec4(color, alpha);',
                    '}',
                    'void main(){',
                    '    vec4 c;',
                    '    mainImage(c, gl_FragCoord.xy);',
                    '    gl_FragColor = c;',
                    '}'
                ].join('\n');

                function compileShader(type, src) {
                    var s = gl.createShader(type);
                    gl.shaderSource(s, src);
                    gl.compileShader(s);
                    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
                        console.error('GridScan shader error:', gl.getShaderInfoLog(s));
                        return null;
                    }
                    return s;
                }

                var vs = compileShader(gl.VERTEX_SHADER, vsSource);
                var fs = compileShader(gl.FRAGMENT_SHADER, fsSource);
                if (!vs || !fs) return;

                var prog = gl.createProgram();
                gl.attachShader(prog, vs);
                gl.attachShader(prog, fs);
                gl.linkProgram(prog);
                if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
                    console.error('GridScan link error:', gl.getProgramInfoLog(prog));
                    return;
                }
                gl.useProgram(prog);

                var buf = gl.createBuffer();
                gl.bindBuffer(gl.ARRAY_BUFFER, buf);
                gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1, 3,-1, -1,3]), gl.STATIC_DRAW);

                var aPos = gl.getAttribLocation(prog, 'aPos');
                if (aPos >= 0) {
                    gl.enableVertexAttribArray(aPos);
                    gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 0, 0);
                }

                var loc_iResolution = gl.getUniformLocation(prog, 'iResolution');
                var loc_iTime = gl.getUniformLocation(prog, 'iTime');
                var loc_uSkew = gl.getUniformLocation(prog, 'uSkew');
                var loc_uTilt = gl.getUniformLocation(prog, 'uTilt');
                var loc_uYaw = gl.getUniformLocation(prog, 'uYaw');
                var loc_uLineThickness = gl.getUniformLocation(prog, 'uLineThickness');
                var loc_uLinesColor = gl.getUniformLocation(prog, 'uLinesColor');
                var loc_uScanColor = gl.getUniformLocation(prog, 'uScanColor');
                var loc_uGridScale = gl.getUniformLocation(prog, 'uGridScale');
                var loc_uLineStyle = gl.getUniformLocation(prog, 'uLineStyle');
                var loc_uLineJitter = gl.getUniformLocation(prog, 'uLineJitter');
                var loc_uScanOpacity = gl.getUniformLocation(prog, 'uScanOpacity');
                var loc_uScanDirection = gl.getUniformLocation(prog, 'uScanDirection');
                var loc_uNoise = gl.getUniformLocation(prog, 'uNoise');
                var loc_uBloomOpacity = gl.getUniformLocation(prog, 'uBloomOpacity');
                var loc_uScanGlow = gl.getUniformLocation(prog, 'uScanGlow');
                var loc_uScanSoftness = gl.getUniformLocation(prog, 'uScanSoftness');
                var loc_uPhaseTaper = gl.getUniformLocation(prog, 'uPhaseTaper');
                var loc_uScanDuration = gl.getUniformLocation(prog, 'uScanDuration');
                var loc_uScanDelay = gl.getUniformLocation(prog, 'uScanDelay');
                var loc_uLightMode = gl.getUniformLocation(prog, 'uLightMode');
                var loc_uScanCount = gl.getUniformLocation(prog, 'uScanCount');

                // Configuration matching GridScan React parameters (but red theme)
                gl.uniform1f(loc_uLineThickness, 1.0);
                gl.uniform3f(loc_uLinesColor, 0.20, 0.12, 0.12); // darker red-grey
                gl.uniform3f(loc_uScanColor, 0.9, 0.2, 0.3); // vivid red/pink
                gl.uniform1f(loc_uGridScale, 0.1);
                gl.uniform1f(loc_uLineStyle, 0.0); // solid
                gl.uniform1f(loc_uLineJitter, 0.1);
                gl.uniform1f(loc_uScanOpacity, 0.4);
                gl.uniform1f(loc_uScanDirection, 2.0); // pingpong
                gl.uniform1f(loc_uNoise, 0.01);
                gl.uniform1f(loc_uBloomOpacity, 0.6);
                gl.uniform1f(loc_uScanGlow, 0.5);
                gl.uniform1f(loc_uScanSoftness, 2.0);
                gl.uniform1f(loc_uPhaseTaper, 0.9);
                gl.uniform1f(loc_uScanDuration, 2.0);
                gl.uniform1f(loc_uScanDelay, 2.0);
                gl.uniform1f(loc_uLightMode, 0.0);
                gl.uniform1f(loc_uScanCount, 0.0);

                var targetSkew = [0, 0], currSkew = [0, 0];
                var targetTilt = 0, currTilt = 0;
                var targetYaw = 0, currYaw = 0;

                document.addEventListener('mousemove', function(e) {
                    var nx = (e.clientX / window.innerWidth) * 2 - 1;
                    var ny = -(e.clientY / window.innerHeight) * 2 + 1;
                    var sensitivity = 0.55;
                    var skewScale = 0.06 + (0.2 - 0.06) * sensitivity;
                    var tiltScale = 0.12 + (0.3 - 0.12) * sensitivity;
                    var yawScale = 0.1 + (0.28 - 0.1) * sensitivity;
                    targetSkew = [nx * skewScale, ny * skewScale * 1.4];
                    targetTilt = -ny * tiltScale;
                    targetYaw = nx * yawScale;
                });

                function resize() {
                    var w = window.innerWidth;
                    var h = window.innerHeight;
                    var dpr = Math.min(window.devicePixelRatio || 1, 2);
                    canvas.width  = w * dpr;
                    canvas.height = h * dpr;
                    gl.viewport(0, 0, canvas.width, canvas.height);
                    gl.uniform3f(loc_iResolution, canvas.width, canvas.height, dpr);
                }
                window.addEventListener('resize', resize);
                resize();

                var startTime = performance.now();
                function render() {
                    var now = performance.now();
                    var time = (now - startTime) * 0.001;
                    
                    // Smooth damp
                    var dt = 0.016;
                    currSkew[0] += (targetSkew[0] - currSkew[0]) * 5.0 * dt;
                    currSkew[1] += (targetSkew[1] - currSkew[1]) * 5.0 * dt;
                    currTilt += (targetTilt - currTilt) * 5.0 * dt;
                    currYaw += (targetYaw - currYaw) * 5.0 * dt;

                    gl.uniform1f(loc_iTime, time);
                    gl.uniform2f(loc_uSkew, currSkew[0], currSkew[1]);
                    gl.uniform1f(loc_uTilt, currTilt);
                    gl.uniform1f(loc_uYaw, currYaw);

                    gl.drawArrays(gl.TRIANGLES, 0, 3);
                    requestAnimationFrame(render);
                }
                requestAnimationFrame(render);
                    if (now - lastFrame < FPS_MS - 0.5) return;
                    lastFrame = now;
                    gl.uniform1f(uTime, (now - startTime) / 1000);
                    gl.drawArrays(gl.TRIANGLES, 0, 3);
                }
                requestAnimationFrame(loop);
            })();

            // 3. Init TextType Welcome
            initTextType(
                document.getElementById('welcome-texttype'),
                [
                    'Selamat datang di RedSim — garda terdepan keamanan digital Anda.',
                    'Analisis kerentanan kode, URL, dan proyek secara otomatis dengan AI.',
                    'Temukan celah keamanan sebelum peretas menemukannya.',
                    'Didukung knowledge base dari MITRE, OWASP, dan NVD.',
                    'Lindungi sistem Anda dengan kecerdasan buatan terpercaya.'
                ],
                { typingSpeed: 45, deletingSpeed: 25, pauseDuration: 2500, loop: true }
            );

            // 4. Init BorderGlow on CTA button
            initBorderGlow('#cta-btn-wrap');

            // 5. Init CardSwap for features section
            var cardSwapContainer = document.getElementById('card-swap-container');
            if (cardSwapContainer && typeof gsap !== 'undefined') {
                initCardSwap(cardSwapContainer, {
                    cardDistance: 55,
                    verticalDistance: 65,
                    delay: 4500,
                    skewAmount: 5,
                });
            }

            // 6. Init Knowledge Base animation
            initKBAnimation();

            // 7. Animate KB stats when in view
            var statsObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, { threshold: 0.2 });
            document.querySelectorAll('.kb-stat').forEach(function(el) {
                el.style.animationPlayState = 'paused';
                statsObserver.observe(el);
            });
        });
    </script>

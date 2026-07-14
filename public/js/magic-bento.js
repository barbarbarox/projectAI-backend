document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap === 'undefined') return;

    // Initialize Magic Bento for any element with .bento-section
    const sections = document.querySelectorAll('.bento-section');
    if (sections.length === 0) return;

    const spotlightRadius = 400;
    const particleCount = 12;
    const glowColor = '132, 0, 255';
    
    // Create Global Spotlight
    const spotlight = document.createElement('div');
    spotlight.className = 'global-spotlight';
    spotlight.style.setProperty('--spotlight-color', glowColor);
    document.body.appendChild(spotlight);

    const calculateSpotlightValues = (radius) => ({
        proximity: radius * 0.5,
        fadeDistance: radius * 0.75
    });

    const createParticleElement = (x, y) => {
        const el = document.createElement('div');
        el.className = 'bento-particle';
        el.style.cssText = `
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: rgba(${glowColor}, 1);
            box-shadow: 0 0 6px rgba(${glowColor}, 0.6);
            pointer-events: none;
            z-index: 100;
            left: ${x}px;
            top: ${y}px;
        `;
        return el;
    };

    sections.forEach(section => {
        const cards = section.querySelectorAll('.magic-bento-card');
        
        cards.forEach(card => {
            card.classList.add('magic-bento-card--border-glow');
            card.style.setProperty('--glow-color', glowColor);
            card.innerHTML = `<div class="particle-container w-full h-full">${card.innerHTML}</div>`;
            
            const container = card.querySelector('.particle-container');
            let isHovered = false;
            let particles = [];
            
            // Initialization of particles on hover
            card.addEventListener('mouseenter', () => {
                isHovered = true;
                const rect = card.getBoundingClientRect();
                
                // Tilt
                gsap.to(card, {
                    rotateX: 5,
                    rotateY: 5,
                    duration: 0.3,
                    ease: 'power2.out',
                    transformPerspective: 1000
                });

                // Particles
                for (let i = 0; i < particleCount; i++) {
                    setTimeout(() => {
                        if (!isHovered) return;
                        const p = createParticleElement(Math.random() * rect.width, Math.random() * rect.height);
                        container.appendChild(p);
                        particles.push(p);

                        gsap.fromTo(p, { scale: 0, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.3, ease: 'back.out(1.7)' });

                        gsap.to(p, {
                            x: (Math.random() - 0.5) * 100,
                            y: (Math.random() - 0.5) * 100,
                            rotation: Math.random() * 360,
                            duration: 2 + Math.random() * 2,
                            ease: 'none',
                            repeat: -1,
                            yoyo: true
                        });

                        gsap.to(p, {
                            opacity: 0.3,
                            duration: 1.5,
                            ease: 'power2.inOut',
                            repeat: -1,
                            yoyo: true
                        });
                    }, i * 100);
                }
            });

            card.addEventListener('mouseleave', () => {
                isHovered = false;
                gsap.to(card, {
                    rotateX: 0,
                    rotateY: 0,
                    x: 0,
                    y: 0,
                    duration: 0.3,
                    ease: 'power2.out'
                });

                particles.forEach(p => {
                    gsap.to(p, {
                        scale: 0,
                        opacity: 0,
                        duration: 0.3,
                        ease: 'back.in(1.7)',
                        onComplete: () => p.remove()
                    });
                });
                particles = [];
            });

            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -10;
                const rotateY = ((x - centerX) / centerX) * 10;

                gsap.to(card, {
                    rotateX,
                    rotateY,
                    duration: 0.1,
                    ease: 'power2.out',
                    transformPerspective: 1000
                });
            });

            card.addEventListener('click', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const maxDistance = Math.max(
                    Math.hypot(x, y),
                    Math.hypot(x - rect.width, y),
                    Math.hypot(x, y - rect.height),
                    Math.hypot(x - rect.width, y - rect.height)
                );

                const ripple = document.createElement('div');
                ripple.style.cssText = `
                    position: absolute;
                    width: ${maxDistance * 2}px;
                    height: ${maxDistance * 2}px;
                    border-radius: 50%;
                    background: radial-gradient(circle, rgba(${glowColor}, 0.4) 0%, rgba(${glowColor}, 0.2) 30%, transparent 70%);
                    left: ${x - maxDistance}px;
                    top: ${y - maxDistance}px;
                    pointer-events: none;
                    z-index: 1000;
                `;

                container.appendChild(ripple);

                gsap.fromTo(
                    ripple,
                    { scale: 0, opacity: 1 },
                    {
                        scale: 1,
                        opacity: 0,
                        duration: 0.8,
                        ease: 'power2.out',
                        onComplete: () => ripple.remove()
                    }
                );
            });
        });

        // Spotlight for this section
        section.addEventListener('mousemove', (e) => {
            const rect = section.getBoundingClientRect();
            const mouseInside = e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom;

            if (!mouseInside) return;

            const { proximity, fadeDistance } = calculateSpotlightValues(spotlightRadius);
            let minDistance = Infinity;

            cards.forEach(card => {
                const cardRect = card.getBoundingClientRect();
                const centerX = cardRect.left + cardRect.width / 2;
                const centerY = cardRect.top + cardRect.height / 2;
                const distance = Math.hypot(e.clientX - centerX, e.clientY - centerY) - Math.max(cardRect.width, cardRect.height) / 2;
                const effectiveDistance = Math.max(0, distance);

                minDistance = Math.min(minDistance, effectiveDistance);

                let glowIntensity = 0;
                if (effectiveDistance <= proximity) {
                    glowIntensity = 1;
                } else if (effectiveDistance <= fadeDistance) {
                    glowIntensity = (fadeDistance - effectiveDistance) / (fadeDistance - proximity);
                }

                const relativeX = ((e.clientX - cardRect.left) / cardRect.width) * 100;
                const relativeY = ((e.clientY - cardRect.top) / cardRect.height) * 100;

                card.style.setProperty('--glow-x', `${relativeX}%`);
                card.style.setProperty('--glow-y', `${relativeY}%`);
                card.style.setProperty('--glow-intensity', glowIntensity.toString());
                card.style.setProperty('--glow-radius', `${spotlightRadius}px`);
            });

            gsap.to(spotlight, {
                left: e.clientX,
                top: e.clientY,
                duration: 0.1,
                ease: 'power2.out'
            });

            const targetOpacity = minDistance <= proximity
                ? 0.8
                : minDistance <= fadeDistance
                    ? ((fadeDistance - minDistance) / (fadeDistance - proximity)) * 0.8
                    : 0;

            gsap.to(spotlight, {
                opacity: targetOpacity,
                duration: targetOpacity > 0 ? 0.2 : 0.5,
                ease: 'power2.out'
            });
        });

        section.addEventListener('mouseleave', () => {
            cards.forEach(card => card.style.setProperty('--glow-intensity', '0'));
            gsap.to(spotlight, { opacity: 0, duration: 0.3, ease: 'power2.out' });
        });
    });
});

const canvas = document.querySelector('[data-three-hero]');

if (canvas) {
    const card = canvas.closest('.interactive-3d');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let startScene;

    const loadWhenVisible = () => {
        if (startScene) return;
        startScene = import('three')
            .then(({ AmbientLight, DirectionalLight, Group, Mesh, MeshPhysicalMaterial, MeshStandardMaterial, PerspectiveCamera, Scene, SphereGeometry, TorusGeometry, WebGLRenderer }) => {
                let renderer;
                try {
                    renderer = new WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: 'low-power' });
                } catch (_) {
                    return;
                }

                const scene = new Scene();
                const camera = new PerspectiveCamera(34, 1, 0.1, 30);
                camera.position.set(0, 0, 5.4);
                const ambient = new AmbientLight(0xffffff, 1.7);
                const keyLight = new DirectionalLight(0xe7f4da, 3.2);
                keyLight.position.set(-2, 3, 5);
                scene.add(ambient, keyLight);

                const objectGroup = new Group();
                scene.add(objectGroup);
                const orb = new Mesh(
                    new SphereGeometry(0.57, 40, 32),
                    new MeshPhysicalMaterial({ color: 0xd9e9ca, roughness: 0.22, metalness: 0.12, clearcoat: 0.8, clearcoatRoughness: 0.2 }),
                );
                objectGroup.add(orb);

                const rings = new Group();
                objectGroup.add(rings);
                const ringMaterial = new MeshStandardMaterial({ color: 0xf2f8ec, metalness: 0.48, roughness: 0.3, transparent: true, opacity: 0.78 });
                [0, 1, 2].forEach((index) => {
                    const ring = new Mesh(new TorusGeometry(0.91 + index * 0.16, 0.012, 8, 100), ringMaterial);
                    ring.rotation.set(0.72 + index * 0.22, 0.38 + index * 0.46, index * 0.4);
                    rings.add(ring);
                });

                const satellite = new Mesh(
                    new SphereGeometry(0.105, 20, 16),
                    new MeshPhysicalMaterial({ color: 0xfff1c7, roughness: 0.2, metalness: 0.18, emissive: 0x5b704b, emissiveIntensity: 0.35 }),
                );
                satellite.position.set(1.15, 0.65, 0.3);
                objectGroup.add(satellite);

                renderer.setClearColor(0x000000, 0);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
                renderer.outputColorSpace = 'srgb';

                const resize = () => {
                    const { width, height } = canvas.getBoundingClientRect();
                    if (!width || !height) return;
                    renderer.setSize(width, height, false);
                    camera.aspect = width / height;
                    camera.updateProjectionMatrix();
                    renderer.render(scene, camera);
                };
                const resizeObserver = new ResizeObserver(resize);
                resizeObserver.observe(canvas);
                resize();

                let targetX = 0;
                let targetY = 0;
                let visible = false;
                let frame = 0;
                let previousFrame = 0;
                const onPointerMove = (event) => {
                    const bounds = card.getBoundingClientRect();
                    targetY = ((event.clientX - bounds.left) / bounds.width - 0.5) * 0.5;
                    targetX = ((event.clientY - bounds.top) / bounds.height - 0.5) * 0.35;
                    card.style.setProperty('--tilt-x', `${-targetX * 5}deg`);
                    card.style.setProperty('--tilt-y', `${targetY * 7}deg`);
                };
                const onPointerLeave = () => {
                    targetX = 0;
                    targetY = 0;
                    card.style.setProperty('--tilt-x', '0deg');
                    card.style.setProperty('--tilt-y', '0deg');
                };
                if (!reduceMotion && window.matchMedia('(pointer: fine)').matches) {
                    card.addEventListener('pointermove', onPointerMove);
                    card.addEventListener('pointerleave', onPointerLeave);
                }

                const visibilityObserver = new IntersectionObserver(([entry]) => {
                    visible = entry.isIntersecting;
                    if (visible && !reduceMotion && !document.hidden) renderer.setAnimationLoop(animate);
                    else renderer.setAnimationLoop(null);
                    if (visible) renderer.render(scene, camera);
                });
                visibilityObserver.observe(canvas);

                const animate = (time) => {
                    if (time - previousFrame < 32) return;
                    previousFrame = time;
                    objectGroup.rotation.x += (targetX - objectGroup.rotation.x) * 0.035;
                    objectGroup.rotation.y += (targetY - objectGroup.rotation.y) * 0.035;
                    if (!reduceMotion) rings.rotation.z += 0.0018;
                    satellite.position.y = 0.65 + Math.sin(time * 0.001) * 0.06;
                    renderer.render(scene, camera);
                };

                document.addEventListener('visibilitychange', () => {
                    if (!visible || document.hidden || reduceMotion) renderer.setAnimationLoop(null);
                    else renderer.setAnimationLoop(animate);
                });
            })
            .catch(() => {});
    };

    if ('IntersectionObserver' in window) {
        const loaderObserver = new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                loaderObserver.disconnect();
                loadWhenVisible();
            }
        }, { rootMargin: '120px' });
        loaderObserver.observe(canvas);
    } else {
        loadWhenVisible();
    }
}

const pageLoader = document.getElementById('page-loader');
const loaderCanvas = document.querySelector('[data-three-loader]');

if (pageLoader && loaderCanvas) {
    window.pageLoaderManaged = true;
    const loadStartedAt = performance.now();
    const progressTrack = document.getElementById('page-loader-progress');
    const progressFill = progressTrack?.querySelector('[data-loader-fill]');
    const walker = pageLoader.querySelector('[data-loader-walker]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let pageReady = document.readyState === 'complete';
    let sceneReady = false;
    let completeRequested = false;
    let loaderVisible = true;
    let progress = 0;
    let previousProgressFrame = 0;
    let fullReachedAt = 0;

    const updateProgress = (value) => {
        const safeValue = Math.max(0, Math.min(100, value));
        if (progressFill) progressFill.style.width = `${safeValue}%`;
        if (progressTrack) progressTrack.setAttribute('aria-valuenow', String(Math.round(safeValue)));
        if (walker) walker.style.left = `${4 + safeValue * 0.92}%`;
    };
    const finishWhenReady = () => {
        if (pageReady && sceneReady) completeRequested = true;
    };
    const animateProgress = (time) => {
        if (!loaderVisible) return;
        const delta = previousProgressFrame ? Math.min((time - previousProgressFrame) / 1000, 0.05) : 0;
        previousProgressFrame = time;
        const target = completeRequested ? 100 : Math.min(92, 5 + (time - loadStartedAt) * 0.022);
        if (reduceMotion) progress = completeRequested ? 100 : progress;
        else progress += (target - progress) * (1 - Math.exp(-delta * 4.8));
        if (completeRequested && progress >= 99.7) {
            progress = 100;
            if (!fullReachedAt) fullReachedAt = time;
            pageLoader.classList.add('is-complete');
        }
        updateProgress(progress);
        if (fullReachedAt && time - fullReachedAt >= 240 && time - loadStartedAt >= 520) {
            window.hidePageLoader?.();
            return;
        }
        requestAnimationFrame(animateProgress);
    };
    requestAnimationFrame(animateProgress);
    window.addEventListener('load', () => {
        pageReady = true;
        finishWhenReady();
    }, { once: true });
    window.setTimeout(() => {
        pageReady = true;
        sceneReady = true;
        finishWhenReady();
    }, 2600);
    window.addEventListener('page-loader:hidden', () => { loaderVisible = false; }, { once: true });

    import('three')
        .then(({ AmbientLight, DirectionalLight, Mesh, MeshPhysicalMaterial, MeshStandardMaterial, PerspectiveCamera, Scene, SphereGeometry, TorusGeometry, WebGLRenderer }) => {
            if (pageLoader.classList.contains('is-hidden')) {
                sceneReady = true;
                finishWhenReady();
                return;
            }

            let renderer;
            try {
                renderer = new WebGLRenderer({ canvas: loaderCanvas, alpha: true, antialias: true, powerPreference: 'low-power' });
            } catch (_) {
                sceneReady = true;
                finishWhenReady();
                return;
            }

            const scene = new Scene();
            const camera = new PerspectiveCamera(38, 1, 0.1, 20);
            camera.position.z = 4.8;
            const isDarkTheme = document.documentElement.classList.contains('dark');
            scene.add(new AmbientLight(0xffffff, isDarkTheme ? 1.35 : 1.8));
            const keyLight = new DirectionalLight(isDarkTheme ? 0xb4d3b8 : 0xe5f2dc, isDarkTheme ? 2.7 : 3.4);
            keyLight.position.set(-2, 3, 4);
            scene.add(keyLight);

            const sculpture = new Mesh(
                new SphereGeometry(0.55, 40, 32),
                new MeshPhysicalMaterial({ color: isDarkTheme ? 0x87a88d : 0xe0ecd7, roughness: 0.16, metalness: 0.08, clearcoat: 1, clearcoatRoughness: 0.14 }),
            );
            scene.add(sculpture);
            const orbit = new Mesh(
                new TorusGeometry(0.98, 0.022, 10, 112),
                new MeshStandardMaterial({ color: isDarkTheme ? 0xd0bb84 : 0xb69a61, metalness: 0.7, roughness: 0.24 }),
            );
            orbit.rotation.set(0.9, 0.3, 0.15);
            scene.add(orbit);
            const orbitTwo = new Mesh(
                new TorusGeometry(1.2, 0.009, 8, 112),
                new MeshStandardMaterial({ color: isDarkTheme ? 0xa5c8ad : 0x789582, metalness: 0.42, roughness: 0.3, transparent: true, opacity: 0.75 }),
            );
            orbitTwo.rotation.set(0.3, 1.1, 0.6);
            scene.add(orbitTwo);

            renderer.setClearColor(0x000000, 0);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
            renderer.outputColorSpace = 'srgb';
            const resize = () => {
                const { width, height } = loaderCanvas.getBoundingClientRect();
                if (!width || !height) return;
                renderer.setSize(width, height, false);
                camera.aspect = width / height;
                camera.updateProjectionMatrix();
                renderer.render(scene, camera);
            };
            const resizeObserver = new ResizeObserver(resize);
            resizeObserver.observe(loaderCanvas);
            resize();
            sceneReady = true;
            finishWhenReady();

            let previousFrame = 0;
            let targetX = 0;
            let targetY = 0;
            const onPointerMove = (event) => {
                targetY = (event.clientX / window.innerWidth - 0.5) * 0.34;
                targetX = (event.clientY / window.innerHeight - 0.5) * 0.22;
            };
            window.addEventListener('pointermove', onPointerMove, { passive: true });
            const animate = (time) => {
                const delta = previousFrame ? Math.min((time - previousFrame) / 1000, 0.05) : 0;
                previousFrame = time;
                const easing = 1 - Math.exp(-delta * 3.2);
                sculpture.rotation.x += (targetX - sculpture.rotation.x) * easing;
                sculpture.rotation.y += (targetY - sculpture.rotation.y) * easing;
                sculpture.rotation.z = Math.sin(time * 0.00042) * 0.045;
                sculpture.position.y = Math.sin(time * 0.00078) * 0.075;
                const breathingScale = 1 + Math.sin(time * 0.0007) * 0.014;
                sculpture.scale.setScalar(breathingScale);
                orbit.rotation.z += delta * 0.2;
                orbit.rotation.x = 0.9 + Math.sin(time * 0.0005) * 0.035;
                orbitTwo.rotation.x -= delta * 0.09;
                orbitTwo.rotation.y = 1.1 + Math.sin(time * 0.00038) * 0.06;
                renderer.render(scene, camera);
            };
            if (!reduceMotion) renderer.setAnimationLoop(animate);
            window.addEventListener('page-loader:hidden', () => {
                loaderVisible = false;
                renderer.setAnimationLoop(null);
                window.removeEventListener('pointermove', onPointerMove);
            }, { once: true });
            document.addEventListener('visibilitychange', () => {
                renderer.setAnimationLoop(!reduceMotion && loaderVisible && !document.hidden ? animate : null);
            });
        })
        .catch(() => {
            sceneReady = true;
            finishWhenReady();
        });
}

let activeDeleteConfirmSceneCleanup = null;

window.startDeleteConfirmScene = (canvas) => {
    activeDeleteConfirmSceneCleanup?.();

    let cancelled = false;
    let cleanupScene = () => {};
    activeDeleteConfirmSceneCleanup = () => {
        cancelled = true;
        cleanupScene();
    };

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    import('three').then((THREE) => {
        if (cancelled || !canvas?.isConnected) return;

        const {
            AmbientLight,
            DirectionalLight,
            Group,
            IcosahedronGeometry,
            Mesh,
            MeshPhysicalMaterial,
            MeshStandardMaterial,
            PerspectiveCamera,
            Scene,
            SphereGeometry,
            TorusGeometry,
            WebGLRenderer,
        } = THREE;

        let renderer;
        try {
            renderer = new WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: 'low-power' });
        } catch (_) {
            return;
        }

        const scene = new Scene();
        const camera = new PerspectiveCamera(42, 1, 0.1, 40);
        camera.position.z = 8.2;
        scene.add(new AmbientLight(0xffffff, 1.55));
        const light = new DirectionalLight(0xffe4e7, 2.5);
        light.position.set(-3, 4, 6);
        scene.add(light);

        const orbitGroup = new Group();
        scene.add(orbitGroup);
        const ringMaterials = [
            new MeshStandardMaterial({ color: 0xfda4af, metalness: 0.5, roughness: 0.26, transparent: true, opacity: 0.58 }),
            new MeshStandardMaterial({ color: 0xfbbf24, metalness: 0.62, roughness: 0.28, transparent: true, opacity: 0.48 }),
        ];
        [2.35, 2.9, 3.45].forEach((radius, index) => {
            const ring = new Mesh(new TorusGeometry(radius, index === 1 ? 0.018 : 0.012, 8, 128), ringMaterials[index % ringMaterials.length]);
            ring.rotation.set(0.6 + index * 0.32, 0.35 + index * 0.42, index * 0.46);
            orbitGroup.add(ring);
        });

        const meshes = [];
        for (let index = 0; index < 24; index += 1) {
            const size = 0.035 + Math.random() * 0.055;
            const material = new MeshPhysicalMaterial({
                color: index % 3 === 0 ? 0xfda4af : 0xfde68a,
                emissive: index % 3 === 0 ? 0x7f1d1d : 0x713f12,
                emissiveIntensity: 0.34,
                roughness: 0.24,
                metalness: 0.24,
            });
            const geometry = index % 4 === 0
                ? new IcosahedronGeometry(size * 1.3, 0)
                : new SphereGeometry(size, 12, 10);
            const mesh = new Mesh(geometry, material);
            mesh.position.set((Math.random() - 0.5) * 9.4, (Math.random() - 0.5) * 5.4, (Math.random() - 0.5) * 1.5);
            mesh.userData.baseY = mesh.position.y;
            mesh.userData.phase = Math.random() * Math.PI * 2;
            mesh.userData.speed = 0.00035 + Math.random() * 0.00045;
            meshes.push(mesh);
            scene.add(mesh);
        }

        renderer.setClearColor(0x000000, 0);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.35));
        renderer.outputColorSpace = 'srgb';
        const resize = () => {
            const { width, height } = canvas.getBoundingClientRect();
            if (!width || !height) return;
            renderer.setSize(width, height, false);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            renderer.render(scene, camera);
        };
        const resizeObserver = new ResizeObserver(resize);
        resizeObserver.observe(canvas);
        resize();

        const animate = (time) => {
            orbitGroup.rotation.z = time * 0.00009;
            orbitGroup.rotation.x = 0.7 + Math.sin(time * 0.00028) * 0.08;
            meshes.forEach((mesh) => {
                mesh.position.y = mesh.userData.baseY + Math.sin(time * mesh.userData.speed + mesh.userData.phase) * 0.16;
                mesh.rotation.x += 0.002;
                mesh.rotation.y += 0.003;
            });
            renderer.render(scene, camera);
        };
        renderer.setAnimationLoop(animate);

        const onVisibilityChange = () => renderer.setAnimationLoop(document.hidden ? null : animate);
        document.addEventListener('visibilitychange', onVisibilityChange);
        cleanupScene = () => {
            renderer.setAnimationLoop(null);
            resizeObserver.disconnect();
            document.removeEventListener('visibilitychange', onVisibilityChange);
            scene.traverse((object) => {
                if (!object.isMesh) return;
                object.geometry.dispose();
                if (Array.isArray(object.material)) object.material.forEach((material) => material.dispose());
                else object.material.dispose();
            });
            renderer.dispose();
        };
    }).catch(() => {});
};

window.stopDeleteConfirmScene = () => {
    activeDeleteConfirmSceneCleanup?.();
    activeDeleteConfirmSceneCleanup = null;
};

const ACCOUNT_KEY = 'skinforgeAccount';
const PROFILE_KEY = 'skinforgeProfile';

const getStoredAccount = () => {
    try {
        const raw = localStorage.getItem(ACCOUNT_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
};

const setStoredAccount = (account) => {
    localStorage.setItem(ACCOUNT_KEY, JSON.stringify(account));
};

const getStoredProfile = () => {
    try {
        const raw = localStorage.getItem(PROFILE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
};

const setStoredProfile = (profile) => {
    localStorage.setItem(PROFILE_KEY, JSON.stringify(profile));
};

const getNextUrl = () => {
    const url = new URL(window.location.href);
    return url.searchParams.get('next') || 'profile.php';
};

const showFeedback = (element, text, type = 'success') => {
    if (!element) {
        return;
    }

    element.hidden = false;
    element.textContent = text;
    element.classList.remove('is-success', 'is-error');
    element.classList.add(type === 'error' ? 'is-error' : 'is-success');
};

const updateAccountUI = () => {
    const account = getStoredAccount();
    const chip = document.querySelector('.js-account-chip');
    const loginLink = document.querySelector('.js-login-link');
    const registerLink = document.querySelector('.js-register-link');
    const logoutButton = document.querySelector('.js-logout-button');

    if (chip) {
        chip.textContent = account ? `Logged in as ${account.username}` : 'Guest mode';
    }

    if (loginLink) {
        loginLink.hidden = Boolean(account);
    }

    if (registerLink) {
        registerLink.hidden = Boolean(account);
    }

    if (logoutButton) {
        logoutButton.hidden = !account;
    }
};

const setupLoader = () => {
    const percent = document.querySelector('.js-loader-percent');
    const message = document.querySelector('.js-loader-message');
    const steps = [
        'Syncing Mojang textures...',
        'Forging 3D skin viewer...',
        'Loading creator profile studio...',
        'Finishing premium transitions...',
    ];

    let value = 12;
    let stepIndex = 0;
    const timer = window.setInterval(() => {
        value = Math.min(value + Math.floor(Math.random() * 18) + 6, 98);
        if (percent) {
            percent.textContent = `${value}%`;
        }
        if (message) {
            message.textContent = steps[stepIndex % steps.length];
        }
        stepIndex += 1;
    }, 180);

    window.addEventListener('load', () => {
        window.clearInterval(timer);
        if (percent) {
            percent.textContent = '100%';
        }
        if (message) {
            message.textContent = 'SkinForge ready';
        }
        document.body.classList.add('is-loaded');
    });
};

const copyWithFeedback = (button, value, successLabel) => {
    if (!button || !value || !navigator.clipboard) {
        return;
    }

    navigator.clipboard.writeText(value).then(() => {
        const original = button.textContent;
        button.textContent = successLabel;
        setTimeout(() => {
            button.textContent = original;
        }, 1400);
    });
};

const buildAuthRedirect = (target, action = 'profile') => {
    const url = new URL(target || 'register.php', window.location.href);
    if (!url.searchParams.get('gate')) {
        url.searchParams.set('gate', action);
    }
    if (!url.searchParams.get('next')) {
        url.searchParams.set('next', window.location.pathname + window.location.search);
    }
    return url.toString();
};

const gateProtectedLinks = () => {
    document.querySelectorAll('.js-auth-gate').forEach((element) => {
        element.addEventListener('click', (event) => {
            const action = element.dataset.authAction || 'premium';
            event.preventDefault();
            window.location.href = buildAuthRedirect(element.getAttribute('href') || 'register.php', action);
        });
    });

    document.querySelectorAll('.js-profile-link').forEach((element) => {
        element.addEventListener('click', (event) => {
            if (getStoredAccount()) {
                event.preventDefault();
                window.location.href = 'profile.php';
                return;
            }

            event.preventDefault();
            window.location.href = buildAuthRedirect('register.php', 'profile');
        });
    });
};

const setupAuthForms = () => {
    const registerForm = document.querySelector('.js-register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const feedback = registerForm.querySelector('.js-auth-feedback');
            const formData = new FormData(registerForm);
            const username = String(formData.get('username') || '').trim();
            const email = String(formData.get('email') || '').trim();
            const password = String(formData.get('password') || '');
            const confirmPassword = String(formData.get('confirm_password') || '');
            const motto = String(formData.get('motto') || '').trim();
            const eraButton = registerForm.querySelector('.btn--year.is-selected');
            const era = eraButton ? eraButton.textContent.trim() : 'Explorer';

            if (!username || !email || !password) {
                showFeedback(feedback, 'Complete username, email and password first.', 'error');
                return;
            }

            if (password !== confirmPassword) {
                showFeedback(feedback, 'Passwords do not match yet.', 'error');
                return;
            }

            const account = {
                username,
                email,
                password,
                motto,
                era,
            };

            setStoredAccount(account);
            setStoredProfile({
                username,
                headline: motto || 'Creator building a premium Minecraft identity inside SkinForge.',
                handle: '@' + username.toLowerCase().replace(/[^a-z0-9]+/g, ''),
                status: era,
                favoriteServer: 'mc.hypixel.net',
                location: 'Global',
                favoriteMode: 'BedWars',
                theme: 'Aurora Mint',
                favoriteCape: 'No cape equipped',
                bio: 'I collect premium Minecraft skins, animated renders and creator-ready showcases in SkinForge.',
                followers: '1.2K',
                collections: '8',
                likes: '540',
                discord: 'Not connected',
                youtube: 'Not connected',
                tiktok: 'Not connected',
            });

            updateAccountUI();
            showFeedback(feedback, 'Account created. Redirecting to your profile studio...', 'success');
            setTimeout(() => {
                window.location.href = getNextUrl();
            }, 700);
        });
    }

    const loginForm = document.querySelector('.js-login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const feedback = loginForm.querySelector('.js-auth-feedback');
            const formData = new FormData(loginForm);
            const identity = String(formData.get('identity') || '').trim();
            const password = String(formData.get('password') || '');
            const account = getStoredAccount();

            if (!account) {
                showFeedback(feedback, 'No account found yet. Create one first.', 'error');
                return;
            }

            const matchesIdentity = account.username === identity || account.email === identity;
            if (!matchesIdentity || account.password !== password) {
                showFeedback(feedback, 'Wrong username/email or password.', 'error');
                return;
            }

            showFeedback(feedback, 'Welcome back. Opening SkinForge...', 'success');
            updateAccountUI();
            setTimeout(() => {
                window.location.href = getNextUrl();
            }, 700);
        });
    }
};

const setupLogout = () => {
    const button = document.querySelector('.js-logout-button');
    if (!button) {
        return;
    }

    button.addEventListener('click', () => {
        localStorage.removeItem(ACCOUNT_KEY);
        updateAccountUI();
        window.location.href = 'index.php';
    });
};

const setupImageShells = () => {
    document.querySelectorAll('.image-shell img').forEach((image) => {
        const shell = image.closest('.image-shell');
        if (!shell) {
            return;
        }

        const markReady = () => shell.classList.add('is-ready');
        if (image.complete) {
            markReady();
        } else {
            image.addEventListener('load', markReady, { once: true });
            image.addEventListener('error', markReady, { once: true });
        }
    });
};

const setupCopyActions = () => {
    document.querySelectorAll('.js-copy-url').forEach((button) => {
        button.addEventListener('click', () => {
            copyWithFeedback(button, window.location.href, 'Link copied');
        });
    });

    document.querySelectorAll('.js-copy-uuid').forEach((button) => {
        button.addEventListener('click', () => {
            copyWithFeedback(button, button.dataset.copyValue, 'UUID copied');
        });
    });
};

const setupYearButtons = () => {
    document.querySelectorAll('.btn--year').forEach((button) => {
        button.addEventListener('click', () => {
            const grid = button.closest('.year-grid');
            if (!grid) {
                return;
            }

            grid.querySelectorAll('.btn--year').forEach((item) => item.classList.remove('is-selected'));
            button.classList.add('is-selected');
        });
    });
};

const setupStageTilt = () => {
    document.querySelectorAll('.js-stage').forEach((stage) => {
        stage.addEventListener('mousemove', (event) => {
            const rect = stage.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width - 0.5) * 3;
            const y = ((event.clientY - rect.top) / rect.height - 0.5) * -3;
            stage.style.setProperty('--stage-rotate-x', `${y}deg`);
            stage.style.setProperty('--stage-rotate-y', `${x}deg`);
        });

        stage.addEventListener('mouseleave', () => {
            stage.style.setProperty('--stage-rotate-x', '0deg');
            stage.style.setProperty('--stage-rotate-y', '0deg');
        });
    });
};

const animationFactory = {
    idle: () => new skinview3d.IdleAnimation(),
    walk: () => {
        const animation = new skinview3d.WalkingAnimation();
        animation.speed = 2.4;
        return animation;
    },
    run: () => {
        const animation = new skinview3d.RunningAnimation();
        animation.speed = 3.4;
        return animation;
    },
    fly: () => {
        const animation = new skinview3d.FlyingAnimation();
        animation.speed = 2.2;
        return animation;
    },
};

const cameraPresets = {
    front: { x: 0, y: 18, z: 34 },
    right: { x: 34, y: 18, z: 0 },
    back: { x: 0, y: 18, z: -34 },
    left: { x: -34, y: 18, z: 0 },
};

const setupViewers = () => {
    document.querySelectorAll('.js-skin-viewer').forEach((stage) => {
        const canvas = stage.querySelector('.js-skin-canvas');
        const fallback = stage.querySelector('.js-render-fallback');
        const controlsScope = stage.parentElement;
        const skinUrl = stage.dataset.skinUrl;

        if (!canvas || !controlsScope || !skinUrl || typeof skinview3d === 'undefined') {
            if (fallback) {
                fallback.hidden = false;
            }
            stage.classList.add('is-2d');
            stage.classList.add('is-ready');
            return;
        }

        const viewer = new skinview3d.SkinViewer({
            canvas,
            width: canvas.width,
            height: canvas.height,
            skin: skinUrl,
        });

        viewer.loadSkin(skinUrl);
        viewer.fov = 44;
        viewer.zoom = 0.86;
        viewer.autoRotate = false;
        viewer.nameTag = null;
        viewer.cameraLight.intensity = 1.2;
        viewer.globalLight.intensity = 0.55;

        const orbit = skinview3d.createOrbitControls(viewer);
        orbit.enableRotate = false;
        orbit.enableZoom = false;
        orbit.enablePan = false;
        const setCameraPreset = (name, animated = false) => {
            const preset = cameraPresets[name] || cameraPresets.front;
            const x = preset.x;
            const y = preset.y;
            const z = preset.z;

            if (viewer.camera?.position) {
                viewer.camera.position.set(x, y, z);
            }

            if (orbit.target) {
                orbit.target.set(0, 18, 0);
                orbit.update();
            }

            if (!animated && viewer.camera?.lookAt) {
                viewer.camera.lookAt(0, 18, 0);
            }
        };
        setCameraPreset('front');

        if (fallback) {
            fallback.hidden = true;
        }

        stage.classList.add('is-ready');

        let currentAnimation = 'idle';
        let currentPan = 'front';
        let currentDisplay = '3d';
        let cycleTimer = null;
        let cinemaMode = false;
        let cinematicTime = 0;
        const modeLabel = stage.querySelector('.js-viewer-mode');
        const cameraLabel = stage.querySelector('.js-viewer-camera');

        const keepViewerCentered = () => {
            cinematicTime += 0.016;

            if (viewer.playerObject?.position) {
                viewer.playerObject.position.x = 0;
                viewer.playerObject.position.z = 0;
            }

            if (cinemaMode) {
                viewer.autoRotate = true;
                viewer.autoRotateSpeed = 0.9;
                viewer.fov = 40 + Math.sin(cinematicTime * 0.7) * 1.5;
                viewer.zoom = 0.9 + Math.sin(cinematicTime * 1.1) * 0.02;
                if (viewer.camera?.position) {
                    viewer.camera.position.x = Math.sin(cinematicTime * 0.6) * 20;
                    viewer.camera.position.z = Math.cos(cinematicTime * 0.6) * 34;
                    viewer.camera.position.y = 18 + Math.sin(cinematicTime) * 1.5;
                    viewer.camera.lookAt(0, 18, 0);
                }
                if (orbit.target) {
                    orbit.target.set(0, 18, 0);
                }
            } else {
                viewer.autoRotate = false;
                viewer.fov = 44;
                viewer.zoom = 0.86;
                setCameraPreset(currentPan, true);
            }

            if (orbit.target) {
                orbit.update();
            }

            window.requestAnimationFrame(keepViewerCentered);
        };

        const updateButtons = () => {
            controlsScope.querySelectorAll('.viewer-control').forEach((button) => {
                const active =
                    button.dataset.viewerDisplay === currentDisplay ||
                    button.dataset.viewerAnimation === currentAnimation ||
                    button.dataset.viewerPan === currentPan ||
                    (button.dataset.viewerCinema === 'true' && cinemaMode) ||
                    (button.dataset.viewerCycle === 'true' && cycleTimer !== null);
                button.classList.toggle('is-active', active);
            });

            if (modeLabel) {
                modeLabel.textContent = `${currentAnimation.toUpperCase()} MODE`;
            }

            if (cameraLabel) {
                cameraLabel.textContent = currentDisplay === '2d'
                    ? '2D RENDER'
                    : (cinemaMode ? 'CINEMA CAM' : `${currentPan.toUpperCase()} CAM`);
            }
        };

        const setDisplayMode = (mode) => {
            currentDisplay = mode === '2d' ? '2d' : '3d';
            stage.classList.toggle('is-2d', currentDisplay === '2d');

            if (currentDisplay === '2d') {
                stopCycle();
                setCinema(false);
                canvas.hidden = true;
                if (fallback) {
                    fallback.hidden = false;
                }
            } else {
                canvas.hidden = false;
                if (fallback) {
                    fallback.hidden = true;
                }
            }

            updateButtons();
        };

        const setAnimation = (name) => {
            if (!animationFactory[name]) {
                return;
            }
            viewer.animation = animationFactory[name]();
            viewer.animation.paused = false;
            currentAnimation = name;
            updateButtons();
        };

        const setPan = (name) => {
            if (!cameraPresets[name]) {
                return;
            }
            currentPan = name;
            setCameraPreset(name);
            updateButtons();
        };

        const applySkin = (skin) => {
            if (!skin?.textureUrl) {
                return;
            }

            const model = skin.variant === 'slim' ? 'slim' : 'default';
            viewer.loadSkin(skin.textureUrl, { model });
            stage.dataset.skinUrl = skin.textureUrl;
            stage.dataset.variant = skin.variant || 'classic';
            stage.dataset.name = skin.owner || skin.name || '';

            if (fallback && skin.frontRenderUrl) {
                fallback.src = skin.frontRenderUrl;
            }

            setDisplayMode('3d');
            setCinema(false);
            setAnimation('idle');
            setPan('front');
        };

        const stopCycle = () => {
            if (cycleTimer !== null) {
                window.clearInterval(cycleTimer);
                cycleTimer = null;
            }
        };

        const setCinema = (enabled) => {
            cinemaMode = enabled;
            stage.classList.toggle('is-cinematic', cinemaMode);
            updateButtons();
        };

        const startCycle = () => {
            const sequence = ['idle', 'walk', 'run', 'fly'];
            let index = 0;
            stopCycle();
            setCinema(true);
            setAnimation(sequence[index]);
            cycleTimer = window.setInterval(() => {
                index = (index + 1) % sequence.length;
                setAnimation(sequence[index]);
                const pans = ['front', 'right', 'back', 'left'];
                setPan(pans[index % pans.length]);
            }, 1600);
            updateButtons();
        };

        setAnimation('idle');
        setPan('front');
        setDisplayMode('3d');
        keepViewerCentered();

        controlsScope.querySelectorAll('.viewer-control').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.dataset.viewerDisplay) {
                    setDisplayMode(button.dataset.viewerDisplay);
                }

                if (button.dataset.viewerAnimation) {
                    setDisplayMode('3d');
                    stopCycle();
                    setCinema(false);
                    setAnimation(button.dataset.viewerAnimation);
                }

                if (button.dataset.viewerPan) {
                    setDisplayMode('3d');
                    stopCycle();
                    setCinema(false);
                    setPan(button.dataset.viewerPan);
                }

                if (button.dataset.viewerCinema === 'true') {
                    setDisplayMode('3d');
                    stopCycle();
                    setCinema(!cinemaMode);
                }

                if (button.dataset.viewerCycle === 'true') {
                    setDisplayMode('3d');
                    if (cycleTimer !== null) {
                        stopCycle();
                        setCinema(false);
                        setAnimation('idle');
                        setPan('front');
                    } else {
                        startCycle();
                    }
                }
            });
        });

        stage.skinforgeViewer = {
            applySkin,
        };
    });
};

const setupProfilePage = () => {
    if (!document.body.classList.contains('page--profile')) {
        return;
    }

    const account = getStoredAccount();
    const gate = document.querySelector('.js-profile-gate');
    const editor = document.querySelector('.js-profile-editor');
    const catalogNode = document.getElementById('profile-skin-catalog');
    const catalog = catalogNode ? JSON.parse(catalogNode.textContent || '[]') : [];

    if (!account) {
        if (gate) {
            gate.hidden = false;
        }
        setTimeout(() => {
            window.location.href = buildAuthRedirect('register.php', 'profile');
        }, 900);
        return;
    }

    const findSkinByQuery = (query) => {
        const normalized = String(query || '').trim().toLowerCase();
        if (!normalized) {
            return null;
        }

        return catalog.find((skin) => {
            const pool = [skin.name, skin.owner, ...(skin.profiles || [])]
                .join(' ')
                .toLowerCase();
            return pool.includes(normalized);
        }) || null;
    };

    const profile = {
        username: account.username,
        headline: account.motto || 'Creator building a premium Minecraft identity inside SkinForge.',
        handle: '@' + account.username.toLowerCase().replace(/[^a-z0-9]+/g, ''),
        status: account.era || 'Verified',
        favoriteServer: 'mc.hypixel.net',
        location: 'Global',
        favoriteMode: 'BedWars',
        theme: 'Aurora Mint',
        favoriteCape: 'No cape equipped',
        bio: 'I collect premium Minecraft skins, animated renders and creator-ready showcases in SkinForge.',
        followers: '1.2K',
        collections: '8',
        likes: '540',
        discord: 'Not connected',
        youtube: 'Not connected',
        tiktok: 'Not connected',
        connectedSkinNickname: account.username,
        ...getStoredProfile(),
    };

    const initialSkin = findSkinByQuery(profile.connectedSkinNickname) || catalog[0] || null;

    const setText = (selector, value) => {
        document.querySelectorAll(selector).forEach((node) => {
            node.textContent = value;
        });
    };

    const applyConnectedSkin = (skin) => {
        if (!skin) {
            return;
        }

        const viewerStage = document.querySelector('.js-profile-stage');
        if (viewerStage?.skinforgeViewer) {
            viewerStage.skinforgeViewer.applySkin(skin);
        }

        const avatar = document.querySelector('.js-profile-avatar');
        if (avatar) {
            avatar.src = `render-skin.php?mode=avatar&seed=${encodeURIComponent(skin.uuid)}&size=88`;
        }

        setText('.js-connected-skin-name', skin.name);
        setText('.js-connected-skin-owner', skin.owner);
        setText('.js-connected-skin-uuid', skin.uuid);
        setText('.js-connected-skin-uuid-panel', skin.uuid);
        setText('.js-connected-skin-variant', skin.variant === 'slim' ? 'Slim' : 'Classic');
        setText('.js-connected-skin-variant-panel', skin.variant === 'slim' ? 'Slim' : 'Classic');
        setText('.js-current-skin-variant', `${skin.variant === 'slim' ? 'Slim' : 'Classic'} model`);

        const command = document.querySelector('.js-head-command');
        if (command) {
            command.textContent = `/give @p minecraft:player_head[profile="${skin.owner}"]`;
        }

        document.querySelectorAll('.js-open-skin-link').forEach((link) => {
            link.href = `index.php?page=skin&skin=${encodeURIComponent(skin.slug)}`;
        });
    };

    setText('.js-profile-name', profile.username);
    setText('.js-profile-headline', profile.headline);
    setText('.js-profile-handle', profile.handle);
    setText('.js-profile-status', `${profile.status} creator profile`);
    setText('.js-profile-status-line', profile.status);
    setText('.js-profile-bio', profile.bio);
    setText('.js-favorite-server', profile.favoriteServer);
    setText('.js-profile-location', profile.location);
    setText('.js-profile-mode', profile.favoriteMode);
    setText('.js-profile-theme', profile.theme);
    setText('.js-profile-cape', profile.favoriteCape);
    setText('.js-profile-discord', profile.discord);
    setText('.js-profile-youtube', profile.youtube);
    setText('.js-profile-tiktok', profile.tiktok);

    const stats = document.querySelectorAll('.profile-hero__stats strong');
    if (stats[0]) stats[0].textContent = profile.followers;
    if (stats[1]) stats[1].textContent = profile.collections;
    if (stats[2]) stats[2].textContent = profile.likes;

    if (editor) {
        editor.hidden = false;
    }

    const form = document.querySelector('.js-profile-form');
    if (!form) {
        return;
    }

    Object.entries(profile).forEach(([key, value]) => {
        const field = form.elements.namedItem(key);
        if (field && 'value' in field) {
            field.value = value;
        }
    });

    if (initialSkin) {
        applyConnectedSkin(initialSkin);
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const feedback = document.querySelector('.js-profile-feedback');
        const formData = new FormData(form);
        const nextProfile = {
            ...getStoredProfile(),
            ...Object.fromEntries(formData.entries()),
        };
        setStoredProfile(nextProfile);
        const accountSnapshot = getStoredAccount();
        if (accountSnapshot) {
            setStoredAccount({
                ...accountSnapshot,
                username: String(nextProfile.username || accountSnapshot.username),
                motto: String(nextProfile.headline || accountSnapshot.motto || ''),
                era: String(nextProfile.status || accountSnapshot.era || ''),
            });
        }
        setText('.js-profile-name', String(nextProfile.username || profile.username));
        setText('.js-profile-headline', String(nextProfile.headline || profile.headline));
        setText('.js-profile-handle', String(nextProfile.handle || profile.handle));
        setText('.js-profile-status', `${String(nextProfile.status || profile.status)} creator profile`);
        setText('.js-profile-status-line', String(nextProfile.status || profile.status));
        setText('.js-profile-bio', String(nextProfile.bio || profile.bio));
        setText('.js-favorite-server', String(nextProfile.favoriteServer || profile.favoriteServer));
        setText('.js-profile-location', String(nextProfile.location || profile.location));
        setText('.js-profile-mode', String(nextProfile.favoriteMode || profile.favoriteMode));
        setText('.js-profile-theme', String(nextProfile.theme || profile.theme));
        setText('.js-profile-cape', String(nextProfile.favoriteCape || profile.favoriteCape));
        setText('.js-profile-discord', String(nextProfile.discord || profile.discord));
        setText('.js-profile-youtube', String(nextProfile.youtube || profile.youtube));
        setText('.js-profile-tiktok', String(nextProfile.tiktok || profile.tiktok));

        const updatedStats = document.querySelectorAll('.profile-hero__stats strong');
        if (updatedStats[0]) updatedStats[0].textContent = String(nextProfile.followers || profile.followers);
        if (updatedStats[1]) updatedStats[1].textContent = String(nextProfile.collections || profile.collections);
        if (updatedStats[2]) updatedStats[2].textContent = String(nextProfile.likes || profile.likes);

        showFeedback(feedback, 'Profile saved in your SkinForge studio.', 'success');
        updateAccountUI();
    });

    const resetButton = document.querySelector('.js-profile-reset');
    if (resetButton) {
        resetButton.addEventListener('click', () => {
            localStorage.removeItem(PROFILE_KEY);
            window.location.reload();
        });
    }

    const connectForm = document.querySelector('.js-skin-connect-form');
    if (connectForm) {
        connectForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const feedback = document.querySelector('.js-skin-connect-feedback');
            const input = connectForm.querySelector('.js-skin-connect-input');
            const skin = findSkinByQuery(input?.value || '');

            if (!skin) {
                showFeedback(feedback, 'Nickname not found in the catalog yet.', 'error');
                return;
            }

            applyConnectedSkin(skin);
            const profileSnapshot = {
                ...getStoredProfile(),
                connectedSkinNickname: skin.owner,
                connectedSkinSlug: skin.slug,
                connectedSkinUuid: skin.uuid,
                connectedSkinVariant: skin.variant,
                connectedSkinOwner: skin.owner,
            };
            setStoredProfile(profileSnapshot);
            showFeedback(feedback, `Connected ${skin.owner} to your profile studio.`, 'success');
        });
    }

    document.querySelectorAll('.js-copy-head').forEach((copyHead) => {
        copyHead.addEventListener('click', () => {
            const command = document.querySelector('.js-head-command');
            copyWithFeedback(copyHead, command?.textContent || '', 'Copied');
        });
    });
};

setupLoader();
updateAccountUI();
setupLogout();
setupCopyActions();
setupYearButtons();
setupImageShells();
gateProtectedLinks();
setupAuthForms();
setupStageTilt();
setupViewers();
setupProfilePage();

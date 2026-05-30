(function () {
    const menuButton = document.querySelector('.menu-toggle');
    const nav = document.querySelector('#main-nav');

    if (menuButton && nav) {
        menuButton.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            menuButton.setAttribute('aria-expanded', String(open));
            menuButton.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
        });

        nav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                nav.classList.remove('is-open');
                menuButton.setAttribute('aria-expanded', 'false');
                menuButton.setAttribute('aria-label', 'Открыть меню');
            });
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (event) => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (!target) {
                return;
            }
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    const revealItems = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealItems.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    document.querySelectorAll('.faq-item').forEach((item) => {
        item.addEventListener('toggle', () => {
            if (!item.open) {
                return;
            }
            document.querySelectorAll('.faq-item[open]').forEach((other) => {
                if (other !== item) {
                    other.open = false;
                }
            });
        });
    });

    const policyDialog = document.querySelector('#policy-dialog');
    const openPolicy = document.querySelector('[data-open-policy]');
    const closePolicy = document.querySelector('[data-close-policy]');

    if (openPolicy && policyDialog) {
        openPolicy.addEventListener('click', () => {
            if (typeof policyDialog.showModal === 'function') {
                policyDialog.showModal();
            } else {
                window.location.href = '/privacy.php';
            }
        });
    }

    if (closePolicy && policyDialog) {
        closePolicy.addEventListener('click', () => policyDialog.close());
    }

    document.querySelectorAll('.lead-form input[name="user_agent"]').forEach((field) => {
        field.value = navigator.userAgent;
    });

    const phonePattern = /^[0-9+\s()\-]{7,30}$/;
    const serviceValues = new Set([
        'spuskaet_koleso',
        'remont_prokola',
        'sezonnyy_shinomontazh',
        'zamena_masla_filtra',
        'zamena_kolodok',
        'melkiy_remont',
        'drugoe',
    ]);

    function digitsOnly(value) {
        return value.replace(/\D+/g, '');
    }

    function setStatus(form, message, type) {
        const status = form.querySelector('.form-status');
        if (!status) {
            return;
        }
        status.textContent = message;
        status.classList.remove('success', 'error');
        if (type) {
            status.classList.add(type);
        }
    }

    function setInvalid(field, invalid) {
        if (!field) {
            return;
        }
        field.classList.toggle('is-invalid', Boolean(invalid));
        if (!invalid) {
            field.removeAttribute('aria-invalid');
        } else {
            field.setAttribute('aria-invalid', 'true');
        }
    }

    function validateForm(form) {
        const name = form.elements.name;
        const phone = form.elements.phone;
        const service = form.elements.service;
        const consent = form.elements.consent;
        const errors = [];

        const nameValue = name.value.trim();
        const phoneValue = phone.value.trim();
        const phoneDigits = digitsOnly(phoneValue);

        setInvalid(name, false);
        setInvalid(phone, false);
        setInvalid(service, false);
        setInvalid(consent, false);

        if (nameValue.length < 2 || nameValue.length > 60) {
            errors.push('Укажите имя от 2 до 60 символов.');
            setInvalid(name, true);
        }

        if (!phonePattern.test(phoneValue) || !/^(7|8)?9\d{9}$/.test(phoneDigits)) {
            errors.push('Укажите телефон в российском формате.');
            setInvalid(phone, true);
        }

        if (!serviceValues.has(service.value)) {
            errors.push('Выберите задачу из списка.');
            setInvalid(service, true);
        }

        if (!consent.checked) {
            errors.push('Нужно согласие на обработку персональных данных.');
            setInvalid(consent, true);
        }

        if (errors.length) {
            setStatus(form, errors[0], 'error');
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
            return false;
        }

        setStatus(form, '', '');
        return true;
    }

    document.querySelectorAll('.lead-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            if (!validateForm(form)) {
                event.preventDefault();
                return;
            }

            if (!window.fetch || !window.FormData) {
                return;
            }

            event.preventDefault();

            if (window.location.hostname.endsWith('github.io')) {
                setStatus(form, 'На GitHub Pages форма открыта в демо-режиме. Для заявки позвоните мастеру напрямую.', 'error');
                return;
            }

            form.classList.add('is-loading');
            setStatus(form, 'Отправляем заявку...', '');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Не удалось отправить заявку.');
                }

                setStatus(form, data.message, 'success');
                form.reset();
                form.querySelectorAll('.is-invalid').forEach((field) => setInvalid(field, false));
                form.querySelector('.form-submit').setAttribute('disabled', 'disabled');
            } catch (error) {
                setStatus(form, error.message || 'Ошибка отправки. Лучше позвонить мастеру напрямую.', 'error');
            } finally {
                form.classList.remove('is-loading');
            }
        });
    });
})();

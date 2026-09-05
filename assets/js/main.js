/**
 * WORDORA — Main JavaScript v2.0
 * Modules: Navbar, Mobile Drawer, GSAP Animations, Swiper, Stats Counter, FAQ, Contact Form
 */

document.addEventListener('DOMContentLoaded', () => {
  // Check reduced motion preference
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ——————————————————————————————————————
     1. NAVBAR — Smart Scroll State & Hide on Active Scroll
     —————————————————————————————————————— */
  const navbar = document.getElementById('mainNavbar');
  if (navbar) {
    let lastScroll = 0;
    let isScrollStoppedTimer = null;

    window.addEventListener('scroll', () => {
      const scrollY = window.scrollY;

      // Scrolled styling (pill background blur & shadow)
      if (scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }

      // If at the top of the page, keep navbar visible
      if (scrollY <= 80) {
        navbar.classList.remove('navbar-hidden');
        if (isScrollStoppedTimer) clearTimeout(isScrollStoppedTimer);
        lastScroll = scrollY;
        return;
      }

      // While actively scrolling, hide navbar smoothly
      navbar.classList.add('navbar-hidden');

      // Clear previous timeout and set debounce to detect when user stops scrolling
      if (isScrollStoppedTimer) clearTimeout(isScrollStoppedTimer);
      isScrollStoppedTimer = setTimeout(() => {
        // User stopped scrolling -> bring navbar back smoothly
        navbar.classList.remove('navbar-hidden');
      }, 180); // 180ms after scrolling stops

      lastScroll = scrollY;
    }, { passive: true });
  }

  /* ——————————————————————————————————————
     2. MOBILE DRAWER
     —————————————————————————————————————— */
  const hamburger = document.getElementById('hamburgerBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const mobileOverlay = document.getElementById('mobileOverlay');
  const mobileClose = document.getElementById('mobileCloseBtn');

  function openDrawer() {
    mobileDrawer?.classList.add('active');
    mobileOverlay?.classList.add('active');
    hamburger?.classList.add('open');
    hamburger?.setAttribute('aria-expanded', 'true');
    document.body.classList.add('menu-open');
  }

  function closeDrawer() {
    mobileDrawer?.classList.remove('active');
    mobileOverlay?.classList.remove('active');
    hamburger?.classList.remove('open');
    hamburger?.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
  }

  hamburger?.addEventListener('click', () => {
    const isOpen = mobileDrawer?.classList.contains('active');
    isOpen ? closeDrawer() : openDrawer();
  });

  mobileClose?.addEventListener('click', closeDrawer);
  mobileOverlay?.addEventListener('click', closeDrawer);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDrawer();
  });

  /* ——————————————————————————————————————
     3. DROPDOWN HOVER INTENT & SMOOTH TRANSITION
     —————————————————————————————————————— */
  const navTriggers = document.querySelectorAll('.nav-dropdown-trigger');
  navTriggers.forEach((trigger) => {
    const dropdown = trigger.querySelector('.nav-dropdown');
    if (!dropdown) return;
    let closeTimer = null;

    const showDropdown = () => {
      if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
      }
      dropdown.classList.add('is-open');
    };

    const hideDropdownWithDelay = () => {
      if (closeTimer) clearTimeout(closeTimer);
      closeTimer = setTimeout(() => {
        dropdown.classList.remove('is-open');
      }, 280); // 280ms grace period for smooth natural cursor movement
    };

    trigger.addEventListener('mouseenter', showDropdown);
    trigger.addEventListener('mouseleave', hideDropdownWithDelay);
    dropdown.addEventListener('mouseenter', showDropdown);
    dropdown.addEventListener('mouseleave', hideDropdownWithDelay);

    // Accessibility support
    trigger.addEventListener('focusin', showDropdown);
    trigger.addEventListener('focusout', hideDropdownWithDelay);
  });

  /* ——————————————————————————————————————
     4. HERO SWIPER SLIDER
     —————————————————————————————————————— */
  const heroSliderEl = document.querySelector('.hero-swiper');
  if (heroSliderEl && typeof Swiper !== 'undefined') {
    const heroSwiper = new Swiper(heroSliderEl, {
      slidesPerView: 1,
      spaceBetween: 0,
      loop: true,
      speed: 800,
      allowTouchMove: true,
      touchRatio: 1.2,
      touchAngle: 45,
      grabCursor: true,
      autoplay: {
        delay: 6000,
        disableOnInteraction: false,
      },
      effect: 'fade',
      fadeEffect: { crossFade: true },
      pagination: {
        el: '.hero-swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.hero-swiper-button-next',
        prevEl: '.hero-swiper-button-prev',
      },
      on: {
        slideChangeTransitionStart: function () {
          if (prefersReducedMotion || typeof gsap === 'undefined') return;
          const activeSlide = this.slides[this.activeIndex];
          if (activeSlide) {
            gsap.fromTo(
              activeSlide.querySelectorAll('.animate-hero-text'),
              { opacity: 0, y: 25 },
              { opacity: 1, y: 0, duration: 0.6, stagger: 0.12, ease: 'power2.out' }
            );
            gsap.fromTo(
              activeSlide.querySelectorAll('.animate-hero-img'),
              { opacity: 0, scale: 0.94, x: 25 },
              { opacity: 1, scale: 1, x: 0, duration: 0.7, ease: 'power2.out', delay: 0.15 }
            );
          }
        }
      }
    });
  }

  /* ——————————————————————————————————————
     4B. SELECTED WORK SWIPER (ARROWS ONLY)
     —————————————————————————————————————— */
  const workSliderEl = document.querySelector('.work-showcase-swiper');
  if (workSliderEl && typeof Swiper !== 'undefined') {
    new Swiper(workSliderEl, {
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      speed: 700,
      autoplay: { delay: 6500, disableOnInteraction: false },
      navigation: {
        nextEl: '.work-showcase-next',
        prevEl: '.work-showcase-prev',
      },
    });
  }

  /* ——————————————————————————————————————
     4C. WHO WE WRITE FOR INDUSTRY SWIPER (ARROWS ONLY)
     —————————————————————————————————————— */
  const industrySliderEl = document.querySelector('.industry-work-swiper');
  if (industrySliderEl && typeof Swiper !== 'undefined') {
    new Swiper(industrySliderEl, {
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      speed: 700,
      autoplay: { delay: 6500, disableOnInteraction: false },
      navigation: {
        nextEl: '.industry-work-next',
        prevEl: '.industry-work-prev',
      },
      on: {
        slideChange: function() {
          const activeSlide = this.slides[this.activeIndex];
          if (activeSlide) {
            activeSlide.querySelectorAll('.stat-count').forEach(el => {
              el.dataset.animated = 'false';
              animateNumber(el);
            });
          }
        }
      }
    });
  }

  /* ——————————————————————————————————————
     ANIMATED NUMBERS & STATS COUNTER
     —————————————————————————————————————— */
  function animateNumber(el) {
    if (el.dataset.animated === 'true') return;
    const target = parseFloat(el.getAttribute('data-count'));
    if (isNaN(target)) return;
    el.dataset.animated = 'true';
    const decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
    const duration = 1800;
    const startTime = performance.now();

    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 4);
      const current = target * ease;
      el.textContent = decimals > 0 ? current.toFixed(decimals) : Math.floor(current).toLocaleString();
      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        el.textContent = decimals > 0 ? target.toFixed(decimals) : target.toLocaleString();
      }
    }
    requestAnimationFrame(update);
  }

  const statObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.querySelectorAll('.stat-count').forEach(animateNumber);
      }
    });
  }, { threshold: 0.2 });

  document.querySelectorAll('.editorial-stats, .industry-work-metrics, .why-split, .industry-work-card').forEach(el => {
    statObserver.observe(el);
  });

  /* ——————————————————————————————————————
     5. TESTIMONIAL SWIPER (Single Stage & Grid)
     —————————————————————————————————————— */
  const stageTestimonialEl = document.querySelector('.testimonial-stage-swiper');
  if (stageTestimonialEl && typeof Swiper !== 'undefined') {
    new Swiper(stageTestimonialEl, {
      slidesPerView: 1,
      spaceBetween: 0,
      loop: true,
      speed: 700,
      autoplay: { delay: 6000, disableOnInteraction: false },
      pagination: { el: '.testimonial-pagination', clickable: true },
      effect: 'fade',
      fadeEffect: { crossFade: true },
    });
  }

  const testimonialEl = document.querySelector('.testimonial-swiper');
  if (testimonialEl && typeof Swiper !== 'undefined') {
    new Swiper(testimonialEl, {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      autoplay: { delay: 5000, disableOnInteraction: false },
      pagination: { el: '.testimonial-pagination', clickable: true },
      breakpoints: {
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 3 },
      }
    });
  }

  /* ——————————————————————————————————————
     6. GSAP ANIMATIONS
     —————————————————————————————————————— */
  if (!prefersReducedMotion && typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);

    // Section reveal — fade up
    gsap.utils.toArray('.reveal-up').forEach(el => {
      gsap.fromTo(el, 
        { opacity: 0, y: 40 },
        {
          scrollTrigger: { trigger: el, start: 'top 88%', once: true },
          opacity: 1, y: 0, duration: 0.7, ease: 'power2.out'
        }
      );
    });

    // Floating decorative icons
    gsap.utils.toArray('.float-icon').forEach(icon => {
      gsap.to(icon, {
        y: -15,
        rotation: gsap.utils.random(-5, 5),
        duration: gsap.utils.random(3, 4.5),
        ease: 'sine.inOut',
        repeat: -1,
        yoyo: true,
      });
    });

    // Hero text & visual animation
    const heroTexts = document.querySelectorAll('.hero .animate-hero-text');
    if (heroTexts.length) {
      gsap.fromTo(heroTexts,
        { opacity: 0, y: 30 },
        { opacity: 1, y: 0, duration: 0.7, stagger: 0.12, ease: 'back.out(1.4)', delay: 0.3 }
      );
    }

    const heroImgs = document.querySelectorAll('.hero .animate-hero-img');
    if (heroImgs.length) {
      gsap.fromTo(heroImgs,
        { opacity: 0, scale: 0.92, x: 30 },
        { opacity: 1, scale: 1, x: 0, duration: 0.85, ease: 'power2.out', delay: 0.4 }
      );
    }

    // Stats counter
    gsap.utils.toArray('.stat-count').forEach(el => {
      const target = parseInt(el.getAttribute('data-count') || el.textContent, 10);
      gsap.from(el, {
        scrollTrigger: { trigger: el, start: 'top 80%', once: true },
        textContent: 0,
        duration: 2,
        snap: { textContent: 1 },
        ease: 'power1.inOut',
      });
    });
  } else {
    // Reduced motion: show all reveal elements immediately
    document.querySelectorAll('.reveal-up, .animate-hero-text').forEach(el => {
      el.style.opacity = '1';
    });
  }

  /* ——————————————————————————————————————
     7. FAQ ACCORDION
     —————————————————————————————————————— */
  document.querySelectorAll('.accordion-trigger').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const item = trigger.closest('.accordion-item');
      const isActive = item.classList.contains('active');

      // Close all
      document.querySelectorAll('.accordion-item.active').forEach(open => {
        open.classList.remove('active');
      });

      // Open clicked if wasn't active
      if (!isActive) {
        item.classList.add('active');
      }
    });
  });

  /* ——————————————————————————————————————
     8. CONTACT FORM — AJAX
     —————————————————————————————————————— */
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      // Clear previous errors
      contactForm.querySelectorAll('.form-error').forEach(err => err.textContent = '');

      const formData = new FormData(contactForm);
      const submitBtn = contactForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Sending...';
      submitBtn.disabled = true;

      try {
        const apiUrl = document.querySelector('meta[name="api-contact-url"]')?.content || '/api/contact.php';
        const response = await fetch(apiUrl, {
          method: 'POST',
          body: formData,
        });
        const result = await response.json();

        if (result.success) {
          // Show success
          const alert = document.createElement('div');
          alert.className = 'alert alert-success';
          alert.innerHTML = '<i class="ri-check-line"></i> ' + result.message;
          contactForm.parentNode.insertBefore(alert, contactForm);
          contactForm.reset();
          setTimeout(() => alert.remove(), 6000);
        } else if (result.errors) {
          Object.entries(result.errors).forEach(([field, msg]) => {
            const errEl = contactForm.querySelector(`[data-error="${field}"]`);
            if (errEl) errEl.textContent = msg;
          });
        }
      } catch (err) {
        console.error('Form submission error:', err);
      }

      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    });
  }

  /* ——————————————————————————————————————
     9. READING PROGRESS BAR (Blog)
     —————————————————————————————————————— */
  const progressBar = document.querySelector('.reading-progress');
  if (progressBar) {
    window.addEventListener('scroll', () => {
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const scrollPercent = (window.scrollY / docHeight) * 100;
      progressBar.style.width = Math.min(scrollPercent, 100) + '%';
    }, { passive: true });
  }

  /* ——————————————————————————————————————
     10. NEWSLETTER FORM — AJAX
     —————————————————————————————————————— */
  document.querySelectorAll('.newsletter__form').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const input = form.querySelector('input[type="email"]');
      const btn = form.querySelector('button');
      if (!input || !input.value.trim()) return;

      const originalBtnText = btn ? btn.textContent : 'Subscribe';
      if (btn) {
        btn.textContent = 'Subscribing...';
        btn.disabled = true;
      }

      try {
        const formData = new FormData();
        formData.append('email', input.value.trim());

        const subUrl = document.querySelector('meta[name="api-subscribe-url"]')?.content || '/api/subscribe.php';
        const res = await fetch(subUrl, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
          input.value = '';
          if (btn) btn.textContent = 'Subscribed!';
          alert(data.message || 'Thank you for subscribing!');
        } else {
          alert(data.message || 'Subscription failed. Please check your email.');
          if (btn) btn.textContent = originalBtnText;
        }
      } catch (err) {
        console.error('Newsletter error:', err);
        if (btn) btn.textContent = originalBtnText;
      }

      setTimeout(() => {
        if (btn) {
          btn.textContent = originalBtnText;
          btn.disabled = false;
        }
      }, 3000);
    });
  });

});




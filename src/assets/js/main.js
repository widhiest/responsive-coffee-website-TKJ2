/*=============== SHOW MENU ===============*/
const navMenu = document.getElementById("nav-menu"),
  navToggle = document.getElementById("nav-toggle"),
  navClose = document.getElementById("nav-close");

/* Menu show */
if (navToggle) {
  navToggle.addEventListener("click", () => {
    navMenu.classList.add("show-menu");
  });
}

/* Menu hidden */
if (navClose) {
  navClose.addEventListener("click", () => {
    navMenu.classList.remove("show-menu");
  });
}

/*=============== REMOVE MENU MOBILE ===============*/
const navLink = document.querySelectorAll(".nav__link");

const linkAction = () => {
  const navMenu = document.getElementById("nav-menu");
  // When we click on each nav__link, we remove the show-menu class
  navMenu.classList.remove("show-menu");
};
navLink.forEach((n) => n.addEventListener("click", linkAction));

/*=============== ADD SHADOW HEADER ===============*/
const shadowHeader = () => {
  const header = document.getElementById("header");
  // Add a class if the bottom offset is greater than 50 of the viewport
  this.scrollY >= 50
    ? header.classList.add("shadow-header")
    : header.classList.remove("shadow-header");
};
window.addEventListener("scroll", shadowHeader);

/*=============== SWIPER POPULAR ===============*/
const swiperPopular = new Swiper(".popular__swiper", {
  loop: true,
  grabCursor: true,
  spaceBetween: 32,
  slidesPerView: "auto",
  centeredSlides: "auto",

  breakpoints: {
    1150: {
      spaceBetween: 80,
    },
  },
});

/*=============== SHOW SCROLL UP ===============*/
const scrollUp = () => {
  const scrollUp = document.getElementById("scroll-up");
  // When the scroll is higher than 350 viewport height, add the show-scroll class to the a tag with the scrollup class
  this.scrollY >= 350
    ? scrollUp.classList.add("show-scroll")
    : scrollUp.classList.remove("show-scroll");
};
window.addEventListener("scroll", scrollUp);

/*=============== SCROLL SECTIONS ACTIVE LINK ===============*/
const sections = document.querySelectorAll("section[id]");

const scrollActive = () => {
  const scrollDown = window.scrollY;

  sections.forEach((current) => {
    const sectionHeight = current.offsetHeight,
      sectionTop = current.offsetTop - 58,
      sectionId = current.getAttribute("id"),
      sectionsClass = document.querySelector(
        ".nav__menu a[href*=" + sectionId + "]"
      );

    if (scrollDown > sectionTop && scrollDown <= sectionTop + sectionHeight) {
      sectionsClass.classList.add("active-link");
    } else {
      sectionsClass.classList.remove("active-link");
    }
  });
};
window.addEventListener("scroll", scrollActive);

/*=============== CART FUNCTIONALITY ===============*/

// Add to cart function
async function addToCart(menuId, menuName) {
  try {
    const button = event.target.closest(".products__button");
    button.classList.add("adding");

    const response = await fetch("cart.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=add&menu_id=${menuId}&quantity=1`,
    });

    const result = await response.json();

    if (result.success) {
      // Update cart count
      updateCartCount(result.cart_count);

      // Show notification
      showCartNotification(menuName);

      // Add success animation
      animateCartIcon();
    }

    setTimeout(() => {
      button.classList.remove("adding");
    }, 600);
  } catch (error) {
    console.error("Error adding to cart:", error);
    // Show error notification
    showErrorNotification("Failed to add item to cart");
  }
}

// Update cart count display
function updateCartCount(count) {
  const cartCount = document.getElementById("cart-count");
  if (cartCount) {
    cartCount.textContent = count;
    cartCount.style.display = count > 0 ? "flex" : "none";

    // Add bounce animation
    cartCount.style.animation = "none";
    setTimeout(() => {
      cartCount.style.animation = "cartBounce 0.5s ease";
    }, 10);
  }
}

// Show cart notification
function showCartNotification(itemName = "Item") {
  const notification = document.getElementById("cart-notification");
  if (notification) {
    const span = notification.querySelector("span");
    if (span) {
      span.textContent = `${itemName} added to cart!`;
    }

    notification.classList.add("show");

    setTimeout(() => {
      notification.classList.remove("show");
    }, 3000);
  }
}

// Show error notification
function showErrorNotification(message) {
  // Create error notification if it doesn't exist
  let errorNotification = document.getElementById("error-notification");
  if (!errorNotification) {
    errorNotification = document.createElement("div");
    errorNotification.id = "error-notification";
    errorNotification.className = "cart-notification";
    errorNotification.style.background = "#e74c3c";
    errorNotification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="ri-error-warning-line"></i>
                <span>${message}</span>
            </div>
        `;
    document.body.appendChild(errorNotification);
  }

  const span = errorNotification.querySelector("span");
  if (span) {
    span.textContent = message;
  }

  errorNotification.classList.add("show");

  setTimeout(() => {
    errorNotification.classList.remove("show");
  }, 3000);
}

// Animate cart icon when item is added
function animateCartIcon() {
  const cartIcon = document.querySelector(".nav__cart");
  if (cartIcon) {
    cartIcon.style.animation = "none";
    setTimeout(() => {
      cartIcon.style.animation = "cartShake 0.5s ease";
    }, 10);
  }
}

// Initialize cart on page load
document.addEventListener("DOMContentLoaded", function () {
  // Update cart count display
  const cartCount = document.getElementById("cart-count");
  if (cartCount) {
    const count = parseInt(cartCount.textContent) || 0;
    cartCount.style.display = count > 0 ? "flex" : "none";
  }

  // Add CSS animations
  const style = document.createElement("style");
  style.textContent = `
        @keyframes cartBounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        @keyframes cartShake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(5deg); }
            75% { transform: rotate(-5deg); }
        }
        
        .products__button.adding {
            animation: pulse 0.6s ease-in-out;
            pointer-events: none;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    `;
  document.head.appendChild(style);
});

// Handle cart page quantity updates
if (window.location.pathname.includes("cart.php")) {
  // Auto-submit quantity forms when input changes
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("quantity-input")) {
      const form = e.target.closest("form");
      if (form) {
        const quantityInput = form.querySelector('input[name="quantity"]');
        if (quantityInput) {
          quantityInput.value = e.target.value;
          form.submit();
        }
      }
    }
  });

  // Add confirmation for remove and clear actions
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-btn")) {
      if (!confirm("Remove this item from cart?")) {
        e.preventDefault();
      }
    }

    if (e.target.textContent.includes("Clear Cart")) {
      if (!confirm("Remove all items from cart?")) {
        e.preventDefault();
      }
    }
  });

  // Validate checkout form
  document.addEventListener("submit", function (e) {
    if (e.target.classList.contains("checkout-form")) {
      const requiredFields = e.target.querySelectorAll(
        "input[required], select[required]"
      );
      let isValid = true;

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false;
          field.style.borderColor = "#e74c3c";
        } else {
          field.style.borderColor = "";
        }
      });

      if (!isValid) {
        e.preventDefault();
        alert("Please fill in all required fields");
      } else {
        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.textContent = "Processing...";
          submitBtn.disabled = true;
        }
      }
    }
  });
}

/*=============== SCROLL REVEAL ANIMATION ===============*/
const sr = ScrollReveal({
  origin: "top",
  distance: "60px",
  duration: 2000,
  delay: 300,
  // reset: true, // Animations repeat
});

sr.reveal(".popular__swiper, .footer__container, .footer__copy");
sr.reveal(".home__shape", { origin: "bottom" });
sr.reveal(".home__coffee", { delay: 1000, distance: "200px", duration: 1500 });
sr.reveal(".home__splash", { delay: 1600, scale: 0, duration: 1500 });
sr.reveal(".home__bean-1, .home__bean-2", {
  delay: 2200,
  scale: 0,
  duration: 1500,
  rotate: { z: 180 },
});
sr.reveal(".home__ice-1, .home__ice-2", {
  delay: 2600,
  scale: 0,
  duration: 1500,
  rotate: { z: 180 },
});
sr.reveal(".home__leaf", {
  delay: 2800,
  scale: 0,
  duration: 1500,
  rotate: { z: 90 },
});
sr.reveal(".home__title", { delay: 3500 });
sr.reveal(".home__data, .home__sticker", { delay: 4000 });
sr.reveal(".about__data", { origin: "left" });
sr.reveal(".about__images", { origin: "right" });
sr.reveal(".about__coffee", { delay: 1000 });
sr.reveal(".about__leaf-1, .about__leaf-2", { delay: 1400, rotate: { z: 90 } });
sr.reveal(".products__card, .contact__info", { interval: 100 });
sr.reveal(".contact__shape", { delay: 600, scale: 0 });
sr.reveal(".contact__delivery", { delay: 1200 });

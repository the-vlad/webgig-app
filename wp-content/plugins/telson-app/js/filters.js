document.addEventListener('DOMContentLoaded', function () {
    const slider = document.getElementById('slider');
    const priceForm = document.getElementById('telson-price-form');
    const toggleBtn = document.getElementById('toggle-price-filter');
    const categoryDropdown = document.querySelector('.custom-category-dropdown');
    
    if (!slider || !priceForm || !categoryDropdown || !toggleBtn) return;
    
    // Hide both by default (optional, in case you want to enforce it)
    priceForm.style.display = 'none';
    categoryDropdown.style.display = 'none';
    
    // Toggle price filter UI and category dropdown
    toggleBtn.addEventListener('click', function () {
        const priceVisible = priceForm.style.display === 'block';
        const categoryVisible = categoryDropdown.style.display === 'block';
    
        priceForm.style.display = priceVisible ? 'none' : 'block';
        categoryDropdown.style.display = categoryVisible ? 'none' : 'block';
    
        priceForm.classList.toggle('slide-in', !priceVisible);
        categoryDropdown.classList.toggle('slide-in', !categoryVisible);
    });
    

    // Init slider
    const min = parseInt(slider.dataset.min);
    const max = parseInt(slider.dataset.max);
    const step = parseFloat(slider.dataset.step);
    const decimals = parseInt(slider.dataset.decimals);
    const prefix = slider.dataset.prefix || '';
    const suffix = slider.dataset.suffix || '';
    const selectedMin = parseInt(slider.dataset.selectedMin);
    const selectedMax = parseInt(slider.dataset.selectedMax);

    noUiSlider.create(slider, {
        start: [selectedMin, selectedMax],
        connect: true,
        step: step,
        range: { min, max },
        format: {
            to: value => value.toFixed(decimals),
            from: value => parseFloat(value)
        }
    });

    const minDisplay = document.getElementById('ta-price-min');
    const maxDisplay = document.getElementById('ta-price-max');
    const inputMin = document.getElementById('ta-hidden-min');
    const inputMax = document.getElementById('ta-hidden-max');

    slider.noUiSlider.on('update', function (values) {
        const [minVal, maxVal] = values.map(Number);
        minDisplay.textContent = prefix + minVal.toFixed(decimals) + suffix;
        maxDisplay.textContent = prefix + maxVal.toFixed(decimals) + suffix;
        inputMin.value = Math.floor(minVal);
        inputMax.value = Math.ceil(maxVal);
    });

    slider.noUiSlider.on('change', runFilterAjax);

    // === TAXONOMY LOGIC WITH PARENT-CHILD DISPLAY CONTROL ===
    const categoryCheckboxes = document.querySelectorAll('.custom-category-item input[type="checkbox"]');

    categoryCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const selectedId = parseInt(this.dataset.termId);
            const selectedParent = parseInt(this.dataset.parent);

            categoryCheckboxes.forEach(box => {
                const boxTermId = parseInt(box.dataset.termId);
                const boxParent = parseInt(box.dataset.parent);
                const item = box.closest('.custom-category-item');

                // Reset visibility before applying logic
                item.style.display = '';

                if (this.checked) {
                    // If a parent is selected, show only its children + itself
                    if (selectedParent === 0) {
                        if (boxParent !== selectedId && boxTermId !== selectedId) {
                            item.style.display = 'none';
                        }
                    }

                    // If a child is selected, show only its parent + itself
                    if (selectedParent > 0) {
                        if (boxTermId !== selectedParent && boxTermId !== selectedId) {
                            item.style.display = 'none';
                        }
                    }
                }
            });

            // Reset visibility if none are checked
            const anyChecked = Array.from(categoryCheckboxes).some(cb => cb.checked);
            if (!anyChecked) {
                categoryCheckboxes.forEach(cb => {
                    cb.closest('.custom-category-item').style.display = '';
                });
            }

            runFilterAjax();
        });
    });

    // Category dropdown toggle
    document.querySelectorAll('.custom-category-label').forEach(label => {
        label.addEventListener('click', () => {
            const dropdown = label.nextElementSibling;
            if (!dropdown) return;
            const isOpen = dropdown.style.display === 'block';
            document.querySelectorAll('.custom-category-body').forEach(el => el.style.display = 'none');
            dropdown.style.display = isOpen ? 'none' : 'block';
        });
    });

    // AJAX filter
    if (typeof jQuery !== 'undefined' && jQuery().select2) {
        jQuery('.telson-select-filter').select2({
            width: '100%',
            placeholder: function() {
                return jQuery(this).data('placeholder');
            }
        });
    }

    // Handle select filter changes
    document.querySelectorAll('.telson-select-filter').forEach(select => {
        select.addEventListener('change', runFilterAjax);
    });

    // Updated AJAX filter function
    function runFilterAjax() {
        const loaderWrapper = document.querySelector('.ta-loader-wrapper');
        if (loaderWrapper) {
            loaderWrapper.style.display = 'flex';
            setTimeout(() => {
                loaderWrapper.style.display = 'none';
            }, 10000);
        }

        const formData = new FormData();
        formData.append('min_price', inputMin.value);
        formData.append('max_price', inputMax.value);
        formData.append('action', 'telson_filter_products2');
        formData.append('nonce', telson_ajax.nonce);

        // Collect selected categories
        document.querySelectorAll('.custom-category-item input[type="checkbox"]:checked').forEach(cb => {
            formData.append(cb.name + '[]', cb.value);
        });

        // Collect selected ACF filters
        document.querySelectorAll('.telson-select-filter').forEach(select => {
            if (select.multiple) {
                Array.from(select.selectedOptions).forEach(option => {
                    if (option.value) {
                        formData.append(select.name, option.value);
                    }
                });
            } else if (select.value) {
                formData.append(select.name, select.value);
            }
        });

        fetch(telson_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(resp => resp.json())
        .then(response => {
            if (response.success && response.data) {
                document.querySelector('.products').innerHTML = response.data.html;
            }
        })
        .catch(err => console.error('AJAX error:', err));
    }

    
});



function updateSelectedTags() {
    document.querySelectorAll('.custom-category-dropdown').forEach(dropdown => {
        const labelContainer = dropdown.querySelector('.custom-category-label');
        const checkboxes = dropdown.querySelectorAll('.custom-category-item input[type="checkbox"]');

        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        labelContainer.innerHTML = ''; // Clear existing content

        if (selected.length === 0) {
            labelContainer.textContent = 'Category';
            return;
        }

        selected.forEach(cb => {
            const tag = document.createElement('span');
            tag.classList.add('selected-tag');
            tag.textContent = cb.dataset.label || cb.value;

            const removeBtn = document.createElement('span');
            removeBtn.classList.add('remove-tag');
            removeBtn.textContent = '×';
            removeBtn.addEventListener('click', () => {
                cb.checked = false;
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            });
            

            tag.appendChild(removeBtn);
            labelContainer.appendChild(tag);
        });
    });
}

// Update tags on checkbox change
document.querySelectorAll('.custom-category-item input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', updateSelectedTags);
});

// Initial update
updateSelectedTags();


document.querySelectorAll('.custom-category-label').forEach(label => {
    label.addEventListener('click', function () {
        const dropdown = this.closest('.custom-category-dropdown');
        const arrow = dropdown.querySelector('.cat-arr');
        arrow.classList.toggle('active-arr');
    });
});


document.addEventListener('click', function (e) {
    const isDropdown = e.target.closest('.custom-category-dropdown');
    const isLabel = e.target.closest('.custom-category-label');

    // If click is outside both dropdown and label, close all dropdowns
    if (!isDropdown && !isLabel) {
        document.querySelectorAll('.custom-category-body').forEach(el => {
            el.style.display = 'none';
        });
    }
});





jQuery(document).ready(function($) {
    $('#close-filters').on('click', function() {
        $('.telson-filters-wrapper').removeClass('ta-show');

      $('.telson-filters-wrapper').addClass('ta-hide');
    });
  
    $('#toggle-price-filter').on('click', function() {
      $('.telson-filters-wrapper').removeClass('ta-hide');
      $('.telson-filters-wrapper').addClass('ta-show');
    });
  });
  














document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('.telson-select-filter');
  
    selects.forEach((select) => {
      const choices = new Choices(select, {
        placeholder: true,
        placeholderValue: select.dataset.placeholder || '',
        removeItemButton: true,
      });
  
      // Remove placeholder from the search input once a selection is made
      select.addEventListener('change', function () {
        const input = select.closest('.choices').querySelector('.choices__input--cloned');
        if (input) {
          if (select.selectedOptions.length > 0) {
            input.placeholder = ''; // Hide placeholder
          } else {
            input.placeholder = select.dataset.placeholder || ''; // Restore if none selected
          }
        }
      });
    });
  });
  



jQuery(function($) {
  function showCouponMessage(message, type = 'error') {
    const $wrapper = $('.coupon-notifications');
    const className = 'woocommerce-message';
    const $wrapper2 = $('#coupon-notifications');
	  
    $wrapper.html(`<ul class="mswc ${className}"><li>${message}</li></ul>`);
	  
  if ($wrapper.length) {
    $('html, body').animate({ scrollTop: $wrapper.offset().top - 100 }, 400);
  }
  }

  function cleanDuplicateHeadings() {
    $('#order_review .order-heading2').remove();
  }

  function removeDuplicateTableWrappers() {
    const $wrappers = $('.table-shop-wrapper');
    if ($wrappers.length > 1) {
      $wrappers.slice(1).remove();
    }
  }

  $('form.checkout').on('click', '.custom-checkout-coupon button[name="apply_coupon"]', function(e) {
    e.preventDefault();

    const $form = $(this).closest('.custom-checkout-coupon');
    const couponCode = $form.find('input[name="coupon_code"]').val().trim();

    if (!couponCode) {
      showCouponMessage('<div class="woocommerce-error">Please enter a coupon code.</div>');
      return;
    }

    $.ajax({
      type: 'POST',
      url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
      data: {
        security: wc_checkout_params.apply_coupon_nonce,
        coupon_code: couponCode
      },
		success: function(response) {
		  cleanDuplicateHeadings();
		  $('body').trigger('update_checkout');

		  setTimeout(function() {
			showCouponMessage(response);
			const $target = $('#coupon-notifications');
			if ($target.length) {
			  $('html, body').animate({ scrollTop: $target.offset().top - 30 }, 300);
			}
		  }, 150);
		},
		error: function() {
		  showCouponMessage('Error applying coupon. Please try again.');
		  const $target = $('.coupon-notifications');
		  if ($target.length) {
			$('html, body').animate({ scrollTop: $target.offset().top - 30 }, 300);
		  }
		}
    });
  });

  $('body').on('updated_checkout', function() {
    cleanDuplicateHeadings();
    removeDuplicateTableWrappers();

    if ($('#order_review .order-heading2').length === 0) {
      $('#order_review').prepend('<h2 class="order-heading2">Your order</h2>');
    }
  });
});

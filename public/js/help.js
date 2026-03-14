// public/js/help.js

document.addEventListener('DOMContentLoaded', function() {
  // FAQ accordion functionality
  const faqQuestions = document.querySelectorAll('.faq-item__question');
  
  faqQuestions.forEach(function(question) {
    question.addEventListener('click', function() {
      const item = this.closest('.faq-item');
      const isOpen = item.classList.contains('is-open');
      
      // Close all other items
      document.querySelectorAll('.faq-item.is-open').forEach(function(openItem) {
        openItem.classList.remove('is-open');
        openItem.querySelector('.faq-item__question').setAttribute('aria-expanded', 'false');
      });
      
      // Toggle current item
      if (!isOpen) {
        item.classList.add('is-open');
        this.setAttribute('aria-expanded', 'true');
      }
    });
  });

  // Help search filter
  const searchInput = document.getElementById('help-search-input');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase();
      const faqItems = document.querySelectorAll('.faq-item');
      
      faqItems.forEach(function(item) {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
      });
    });
  }
});

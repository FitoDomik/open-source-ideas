document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
    updateThemeIcon(savedTheme);
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
        });
    } else {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            }
        });
    }, {
        rootMargin: '50px 0px',
        threshold: 0.01
    });
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Файл слишком большой. Максимальный размер: 5 МБ');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const oldPreview = input.parentElement.querySelector('.image-preview');
                    if (oldPreview) {
                        oldPreview.remove();
                    }
                    const preview = document.createElement('div');
                    preview.className = 'image-preview';
                    preview.style.marginTop = '10px';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '300px';
                    img.style.borderRadius = '8px';
                    img.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                    preview.appendChild(img);
                    input.parentElement.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Вы уверены?')) {
                e.preventDefault();
            }
        });
    });
});
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});
function toggleLike(ideaId, button) {
    fetch('ajax_like.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'idea_id=' + ideaId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('liked', data.liked);
            button.querySelector('.like-count').textContent = data.count;
        } else {
            if (data.message.includes('войти')) {
                window.location.href = 'login.php';
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
function addComment(ideaId) {
    const form = document.getElementById('comment-form');
    const formData = new FormData(form);
    formData.append('action', 'add');
    formData.append('idea_id', ideaId);
    fetch('ajax_comment.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
    return false;
}
function changeStatus(ideaId, status) {
    fetch('ajax_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'idea_id=' + ideaId + '&status=' + status
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
    updateThemeIcon(savedTheme);
});
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
}
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
}
function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
        themeToggle.title = theme === 'dark' ? 'Светлая тема' : 'Темная тема';
    }
}
function toggleFavorite(ideaId, button) {
    fetch('ajax_favorite.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'idea_id=' + ideaId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('favorited', data.favorited);
            button.innerHTML = data.favorited ? '⭐' : '☆';
            button.title = data.favorited ? 'Удалить из избранного' : 'Добавить в избранное';
        }
    })
    .catch(error => console.error('Error:', error));
}
function toggleSubscribe(userId, button) {
    fetch('ajax_subscribe.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'user_id=' + userId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('subscribed', data.subscribed);
            button.textContent = data.subscribed ? '🔔 Подписка' : '➕ Подписаться';
        }
    })
    .catch(error => console.error('Error:', error));
}
document.addEventListener('DOMContentLoaded', function () {
    // Sistema de calificación con SVG y animación
    document.querySelectorAll('.rating-container').forEach(container => {
        const stars = container.querySelectorAll('.rating-star');
        const recipeId = container.getAttribute('data-recipe-id');

        stars.forEach(star => {
            const value = star.getAttribute('data-value');

            // Click
            star.addEventListener('click', function () {
                stars.forEach(s => {
                    s.classList.toggle('active', s.getAttribute('data-value') <= value);
                });

                submitRating(recipeId, value);

                // Animación pop
                stars.forEach(s => {
                    if (s.classList.contains('active')) {
                        s.classList.add('pop');
                        setTimeout(() => s.classList.remove('pop'), 400);
                    }
                });
            });

            // Hover
            star.addEventListener('mouseenter', function () {
                stars.forEach(s => {
                    s.classList.toggle('hover', s.getAttribute('data-value') <= value);
                });
            });

            star.addEventListener('mouseleave', function () {
                stars.forEach(s => s.classList.remove('hover'));
            });
        });
    });

    function submitRating(recipeId, rating) {
        fetch('api/ratings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                recipe_id: recipeId,
                rating: rating
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const avgRatingElement = document.getElementById('average-rating');
                if (avgRatingElement) {
                    avgRatingElement.textContent = data.average_rating.toFixed(1);
                }
                showNotification('¡Gracias por tu calificación!', 'success');
            } else {
                showNotification('Error al enviar calificación: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al enviar calificación', 'error');
        });
    }

    // Sistema de comentarios
    const commentForm = document.getElementById('commentForm');

    if (commentForm) {
        commentForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const recipeId = this.getAttribute('data-recipe-id');
            const commentText = document.getElementById('commentText').value;

            if (commentText.trim() === '') {
                showNotification('Por favor, escribe un comentario', 'error');
                return;
            }

            submitComment(recipeId, commentText);
        });
    }

    function submitComment(recipeId, comment) {
        fetch('api/comments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                recipe_id: recipeId,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('commentText').value = '';
                addCommentToList(data.comment);
                showNotification('Comentario publicado correctamente', 'success');
            } else {
                showNotification('Error al publicar comentario: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al publicar comentario', 'error');
        });
    }

    function addCommentToList(comment) {
        const commentsList = document.querySelector('.comments-list');

        if (commentsList) {
            const commentElement = document.createElement('div');
            commentElement.className = 'comment fade-in';

            commentElement.innerHTML = `
                <div class="comment-header">
                    <span class="comment-author">
                        <i class="fas fa-user-circle"></i> ${escapeHtml(comment.user_name)}
                    </span>
                    <span class="comment-date">${comment.formatted_date}</span>
                </div>
                <div class="comment-body">
                    <p>${escapeHtml(comment.text).replace(/\n/g, '<br>')}</p>
                </div>
            `;

            const noComments = document.querySelector('.no-comments');
            if (noComments) noComments.remove();

            commentsList.prepend(commentElement);
        }
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function (char) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[char];
        });
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
});

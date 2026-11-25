<x-layouts::app.sidebar :title="$title ?? null">
    {{ $slot }}
    <div class="flex mt-5">
        <x-mary-button
                label="Handled with Love"
                icon="s-heart"
                class="btn btn-outline btn-secondary love-button"
                onclick="burstHearts(event)"
        />
    </div>
</x-layouts::app.sidebar>

<style>
    .heart-effect {
        position: fixed;
        pointer-events: none;
        z-index: 10000;
        animation: floatHeart 1s ease-out forwards;
    }

    @keyframes floatHeart {
        0% {
            transform: translate(0, 0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(var(--tx, 0), -100px) scale(0);
            opacity: 0;
        }
    }
</style>

<script>
    function burstHearts(event) {
        const button = event.currentTarget;
        const rect = button.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        // Cantidad de corazones (entre 8 y 15)
        const heartCount = Math.floor(Math.random() * 8) + 8;

        for(let i = 0; i < heartCount; i++) {
            const angle = (Math.PI * 2 * i) / heartCount;
            const speed = 50 + Math.random() * 80;
            const tx = Math.cos(angle) * speed;
            const ty = Math.sin(angle) * speed - 80;

            const heart = document.createElement('div');
            heart.className = 'heart-effect';
            heart.innerHTML = ['❤️', '💖', '💗', '💓', '💕', '💝'][Math.floor(Math.random() * 6)];
            heart.style.left = centerX + 'px';
            heart.style.top = centerY + 'px';
            heart.style.fontSize = (Math.random() * 20 + 20) + 'px';
            heart.style.setProperty('--tx', tx + 'px');
            heart.style.transform = `translate(${tx}px, ${ty}px)`;

            document.body.appendChild(heart);

            setTimeout(() => {
                heart.remove();
            }, 1000);
        }
    }
</script>
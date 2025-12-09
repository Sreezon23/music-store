class GalleryView {
    constructor() {
        this.currentPage = 1;
        this.isLoading = false;
        this.setupInfiniteScroll();
    }

    setupInfiniteScroll() {
        const gallery = document.getElementById('galleryContainer');
        window.addEventListener('scroll', () => {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
                if (!this.isLoading) {
                    this.loadMore();
                }
            }
        });
    }

    async loadMore() {
        const state = window.appState.getState();
        if (this.isLoading) return;

        this.isLoading = true;
        document.getElementById('loadingIndicator').style.display = 'block';

        try {
            const response = await fetch(
                `/api.php?action=getSongs&seed=${state.seed}&locale=${state.locale}&likes=${state.likes}&page=${this.currentPage + 1}&pageSize=20`
            );
            const data = await response.json();

            const gallery = document.getElementById('galleryContainer');
            data.songs.forEach((song) => {
                gallery.appendChild(this.createCard(song));
            });

            this.currentPage++;
        } catch (error) {
            console.error('Error loading more songs:', error);
        } finally {
            this.isLoading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
        }
    }

    render(songs, page) {
        const gallery = document.getElementById('galleryContainer');
        gallery.innerHTML = '';
        this.currentPage = page;

        songs.forEach((song) => {
            gallery.appendChild(this.createCard(song));
        });
    }

    createCard(song) {
        const card = document.createElement('div');
        card.className = 'gallery-card';

        const colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe'];
        const color = colors[song.index % colors.length];

        card.innerHTML = `
            <div class="gallery-card-cover" style="background: linear-gradient(135deg, ${color}, ${color}dd);">
                <strong>${song.title}</strong>
            </div>
            <div class="gallery-card-info">
                <div class="gallery-card-title">${song.title}</div>
                <div class="gallery-card-artist">${song.artist}</div>
                <div class="gallery-card-meta">
                    <span>${song.genre}</span>
                    <span>❤️ ${song.likes}</span>
                </div>
            </div>
        `;

        card.addEventListener('click', () => {
            window.appState.openModal(song);
        });

        return card;
    }
}

window.galleryView = new GalleryView();

class AppState {
    constructor() {
        this.state = {
            seed: 12345,
            locale: 'en_US',
            likes: 5,
            currentView: 'table',
            tablePage: 1,
            galleryPage: 1,
            loading: false
        };

        this.currentSongs = [];
        this.expandedRow = null;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadInitialData();
    }

    setupEventListeners() {
        // Toolbar events
        document.getElementById('locale').addEventListener('change', (e) => {
            this.setState({ locale: e.target.value, tablePage: 1, galleryPage: 1 });
            this.loadData();
        });

        document.getElementById('seed').addEventListener('change', (e) => {
            this.setState({ seed: parseInt(e.target.value) || 0, tablePage: 1, galleryPage: 1 });
            this.loadData();
        });

        document.getElementById('randomSeedBtn').addEventListener('click', () => {
            const randomSeed = Math.floor(Math.random() * Number.MAX_SAFE_INTEGER);
            document.getElementById('seed').value = randomSeed;
            this.setState({ seed: randomSeed, tablePage: 1, galleryPage: 1 });
            this.loadData();
        });

        document.getElementById('likes').addEventListener('change', (e) => {
            this.setState({ likes: parseFloat(e.target.value) || 0 });
            this.loadData();
        });

        // View toggle
        document.getElementById('tableViewBtn').addEventListener('click', () => {
            this.switchView('table');
        });

        document.getElementById('galleryViewBtn').addEventListener('click', () => {
            this.switchView('gallery');
        });

        // Modal close
        document.querySelector('.close-btn').addEventListener('click', () => {
            this.closeModal();
        });

        document.getElementById('expandedModal').addEventListener('click', (e) => {
            if (e.target.id === 'expandedModal') {
                this.closeModal();
            }
        });
    }

    setState(updates) {
        this.state = { ...this.state, ...updates };
    }

    async loadInitialData() {
        await this.loadData();
    }

    async loadData() {
        this.state.loading = true;

        if (this.state.currentView === 'table') {
            await this.loadTableData();
        } else {
            await this.loadGalleryData();
        }

        this.state.loading = false;
    }

    async loadTableData() {
        try {
            const response = await fetch(
                `/api.php?action=getSongs&seed=${this.state.seed}&locale=${this.state.locale}&likes=${this.state.likes}&page=${this.state.tablePage}&pageSize=10`
            );
            const data = await response.json();
            this.currentSongs = data.songs;
            window.tableView?.render(data.songs, this.state.tablePage);
        } catch (error) {
            console.error('Error loading table data:', error);
        }
    }

    async loadGalleryData() {
        try {
            const response = await fetch(
                `/api.php?action=getSongs&seed=${this.state.seed}&locale=${this.state.locale}&likes=${this.state.likes}&page=${this.state.galleryPage}&pageSize=20`
            );
            const data = await response.json();
            this.currentSongs = data.songs;
            window.galleryView?.render(data.songs, this.state.galleryPage);
        } catch (error) {
            console.error('Error loading gallery data:', error);
        }
    }

    switchView(view) {
        this.setState({ currentView: view });

        // Update button styles
        document.getElementById('tableViewBtn').classList.toggle('active', view === 'table');
        document.getElementById('galleryViewBtn').classList.toggle('active', view === 'gallery');

        // Update view visibility
        document.getElementById('tableView').classList.toggle('active', view === 'table');
        document.getElementById('galleryView').classList.toggle('active', view === 'gallery');

        if (view === 'table') {
            this.state.tablePage = 1;
            this.loadTableData();
        } else {
            this.state.galleryPage = 1;
            this.loadGalleryData();
        }
    }

    openModal(song) {
        document.getElementById('modalTitle').textContent = song.title;
        document.getElementById('modalArtist').textContent = song.artist;
        document.getElementById('modalAlbum').textContent = song.album;
        document.getElementById('modalGenre').textContent = song.genre;
        document.getElementById('modalLikes').textContent = song.likes;
        document.getElementById('modalReview').textContent = song.review;

        // Generate album cover with title and artist
        const cover = document.getElementById('albumCover');
        const colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe'];
        const color = colors[song.index % colors.length];
        cover.style.background = `linear-gradient(135deg, ${color} 0%, ${color}dd 100%)`;
        cover.innerHTML = `<div style="text-align: center;"><strong>${song.title}</strong><br>${song.artist}</div>`;

        document.getElementById('expandedModal').classList.remove('hidden');

        // Setup play button
        document.getElementById('playBtn').onclick = () => this.playAudio(song);
    }

    closeModal() {
        document.getElementById('expandedModal').classList.add('hidden');
    }

    async playAudio(song) {
        const btn = document.getElementById('playBtn');
        btn.disabled = true;
        btn.textContent = '⏳ Generating...';

        try {
            const response = await fetch(
                `/api.php?action=getAudio&seed=${this.state.seed}&index=${song.index}`
            );
            const data = await response.json();

            const audio = document.getElementById('audioPlayer');
            audio.src = data.audio;
            audio.play();

            btn.textContent = '⏸ Playing...';

            audio.onended = () => {
                btn.disabled = false;
                btn.textContent = '▶ Play Preview';
            };
        } catch (error) {
            console.error('Error playing audio:', error);
            btn.disabled = false;
            btn.textContent = '▶ Play Preview';
        }
    }

    getState() {
        return this.state;
    }
}

// Initialize app
window.appState = new AppState();

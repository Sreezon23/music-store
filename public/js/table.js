class TableView {
    render(songs, page) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        songs.forEach((song) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${song.index}</td>
                <td>${song.title}</td>
                <td>${song.artist}</td>
                <td>${song.album}</td>
                <td>${song.genre}</td>
                <td>${song.likes}</td>
            `;

            row.addEventListener('click', () => {
                window.appState.openModal(song);
            });

            tbody.appendChild(row);
        });

        const pageInfo = document.getElementById('pageInfo');
        pageInfo.textContent = `Page ${page}`;

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        prevBtn.disabled = page <= 1;
        prevBtn.onclick = () => {
            window.appState.setState({ tablePage: page - 1 });
            window.appState.loadData();
        };

        nextBtn.onclick = () => {
            window.appState.setState({ tablePage: page + 1 });
            window.appState.loadData();
        };
    }
}

window.tableView = new TableView();

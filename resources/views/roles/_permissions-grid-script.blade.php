<script>
    document.getElementById('select-all')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('clear-all')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    });
    document.querySelectorAll('.row-all').forEach(button => {
        button.addEventListener('click', () => {
            const boxes = document.querySelectorAll('.row-' + button.dataset.row);
            const allChecked = Array.from(boxes).every(cb => cb.checked);
            boxes.forEach(cb => cb.checked = !allChecked);
        });
    });
</script>

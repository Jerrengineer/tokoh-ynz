            </main>
        </div>
    </div>

    <!-- Toggle Sidebar Mobile JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
                        if (!sidebar.contains(e.target) && e.target !== menuToggle) {
                            sidebar.classList.remove('show');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>

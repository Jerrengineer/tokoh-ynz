            </main>
        </div>
    </div>

    <!-- Toggle Sidebar Mobile JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            
            function openSidebar() {
                if (sidebar) sidebar.classList.add('show');
                if (sidebarBackdrop) sidebarBackdrop.classList.add('show');
                document.body.classList.add('sidebar-open');
            }
            
            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('show');
                if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (sidebar && sidebar.classList.contains('show')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }
            
            if (sidebarClose) {
                sidebarClose.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeSidebar();
                });
            }
            
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function() {
                    closeSidebar();
                });
            }
            
            // Close sidebar on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            });
            
            // Close sidebar on window resize if larger than 1024px
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024 && sidebar && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>

    <footer class="py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 text-center mb-2">
                    <div class="align-items-center">
                        <div>
                            <p class="mb-0 fw-bold">&copy; <?php echo date('Y');?> Github Sealt
                    </a> </p>
                            <div class="d-flex justify-content-center align-items-end my-1">
                            <p class="text-muted small mb-0">BENINS</p>
                            <a href="https://github.com/Sealt/cursor-takeout-reviews" target="_blank">
                            <img 
                                src="https://img.shields.io/github/stars/Sealt/cursor-takeout-reviews?style=flat-square&color=yellow" 
                                alt="GitHub Stars" 
                                style="vertical-align: middle; margin: 0 4px;"
                            /></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-center">
                    <div class="text-muted small">
                        <p class="mb-0">由 Cursor + PHP + SQLite 强力驱动</p>
                        <p class="mb-0">
                            <span id="visit-count"><span><?php echo number_format($siteStats['online_count']); ?></span> 在线 <?php echo number_format($siteStats['total_visitors']); ?></span> 次访问
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="<?php echo getBaseUrl(); ?>assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
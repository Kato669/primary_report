        </div>
      </div>
    </div>
    <div class="hidden"></div>
    <?php
  // Show the marquee footer only on the login page and the index (dashboard) page
  $current_page = basename($_SERVER['PHP_SELF']);
  if($current_page === 'login.php' || $current_page === 'index.php'):
    ?>
    <div class="container-fluid footer mt-5">
      <div class="row">
        <div class="col-lg-12 text-center mb-0 py-2" style="background-color: #001870;">
            <marquee behavior="alternate" scrollamount="3" style="color: white;">© <?php echo date('Y'); ?> EduMaster Uganda. All Rights Reserved.</marquee>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <!-- data table -->
    

    <!-- js for database -->
    <script src="https://cdn.datatables.net/2.3.3/js/dataTables.js"></script>
    
    <script src="js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
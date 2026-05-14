<script src="{{ asset('admin/js/core/popper.min.js') }}"></script>
<script src="{{ asset('admin/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('admin/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script src="{{ asset('admin/js/plugins/chartjs.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
  var win = navigator.platform.indexOf('Win') > -1;
  if (win && document.querySelector('#sidenav-scrollbar')) {
    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), { damping: '0.5' });
  }
</script>
<script>
  // Admin theme toggle
  (function () {
    function applyIcons(theme) {
      document.querySelectorAll('[data-admin-theme-toggle]').forEach(function (btn) {
        var moon = btn.querySelector('.admin-theme-icon-moon');
        var sun = btn.querySelector('.admin-theme-icon-sun');
        if (moon) moon.classList.toggle('d-none', theme === 'dark');
        if (sun) sun.classList.toggle('d-none', theme !== 'dark');
      });
    }
    document.addEventListener('DOMContentLoaded', function () {
      var current = document.body.getAttribute('data-theme') || 'light';
      applyIcons(current);
      document.querySelectorAll('[data-admin-theme-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var now = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
          var next = now === 'dark' ? 'light' : 'dark';
          document.body.setAttribute('data-theme', next);
          document.documentElement.setAttribute('data-admin-theme', next);
          try { window.localStorage.setItem('admin_theme', next); } catch (e) {}
          applyIcons(next);
        });
      });
    });
  })();
</script>
<script>
  // Sidebar toggle for mobile
  (function () {
    document.addEventListener('DOMContentLoaded', function () {
      var toggle = document.getElementById('iconNavbarSidenav');
      var iconClose = document.getElementById('iconSidenav');
      var sidenav = document.getElementById('sidenav-main');
      if (!sidenav) return;

      var backdrop = document.createElement('div');
      backdrop.className = 'admin-sidenav-backdrop';
      document.body.appendChild(backdrop);

      function close() { document.body.classList.remove('g-sidenav-pinned'); }
      function toggleSidebar(e) {
        if (e) e.preventDefault();
        document.body.classList.toggle('g-sidenav-pinned');
      }

      if (toggle) toggle.addEventListener('click', toggleSidebar);
      if (iconClose) iconClose.addEventListener('click', close);
      backdrop.addEventListener('click', close);

      sidenav.querySelectorAll('a.admin-sidenav__link:not([data-bs-toggle]), a.admin-sidenav__sublink')
        .forEach(function (link) {
          link.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 1199.98px)').matches) close();
          });
        });

      window.addEventListener('resize', function () {
        if (window.matchMedia('(min-width: 1200px)').matches) close();
      });
    });
  })();
</script>
@if (session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), confirmButtonText: 'OK' });
    });
  </script>
@endif
@if (session('error'))
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), confirmButtonText: 'OK' });
    });
  </script>
@endif
@if ($errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      Swal.fire({ icon: 'error', title: 'Validasi gagal', html: @json($errors->all()).join('<br>'), confirmButtonText: 'OK' });
    });
  </script>
@endif
<script>
  // DataTables init
  document.addEventListener('DOMContentLoaded', function () {
    var tables = document.querySelectorAll('table.js-datatable');
    if (typeof $.fn.dataTable !== 'undefined' && tables.length) {
      var dtBase = {
        responsive: false,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
        order: [],
        columnDefs: [
          { targets: '_all', className: 'align-middle' },
          { targets: -1, orderable: false, searchable: false, className: 'text-end dt-actions align-middle' }
        ],
        dom: "<'row align-items-end gy-2 mb-2'<'col-md-6'l><'col-md-6'f>><'row'<'col-12'tr>><'row align-items-center gy-2 mt-2'<'col-md-6'i><'col-md-6'p>>"
      };
      tables.forEach(function (table) {
        if (!$.fn.dataTable.isDataTable(table)) {
          $(table).DataTable(dtBase);
        }
      });
    }

    // SweetAlert delete confirmation
    document.querySelectorAll('.btn-delete-swal').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = this.closest('form');
        if (!form) return;
        Swal.fire({
          title: this.getAttribute('data-title') || 'Hapus data ini?',
          text: 'Data yang dihapus tidak bisa dikembalikan.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonText: 'Batal',
          confirmButtonText: 'Ya, hapus'
        }).then(function (result) {
          if (result.isConfirmed) form.submit();
        });
      });
    });
  });
</script>
@stack('scripts')
<script src="{{ asset('admin/js/argon-dashboard.min.js') }}"></script>

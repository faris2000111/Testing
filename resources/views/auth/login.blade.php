<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('admin/img/apple-icon.png') }}">
  <link rel="icon" type="image/png" href="{{ asset('admin/img/favicon.png') }}">
  <title>Login - Admin</title>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <link href="{{ asset('admin/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('admin/css/nucleo-svg.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link id="pagestyle" href="{{ asset('admin/css/argon-dashboard.css') }}" rel="stylesheet" />
</head>
<body>
  <main class="main-content mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
              <div class="card card-plain">
                <div class="card-header pb-0 text-start">
                  <h4 class="font-weight-bolder">Sign In</h4>
                  <p class="mb-0">Masukkan email dan password untuk masuk ke admin panel</p>
                </div>
                <div class="card-body">
                  @if ($errors->any())
                    <div class="alert alert-danger text-white" role="alert">
                      {{ $errors->first() }}
                    </div>
                  @endif
                  <form role="form" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                      <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" placeholder="Email" aria-label="Email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                      <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="Password" aria-label="Password" required>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" {{ old('remember') ? 'checked' : '' }}>
                      <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <div class="text-center">
                      <button type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0">Sign in</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden">
                <span class="mask bg-gradient-primary opacity-6"></span>
                <h4 class="mt-5 text-white font-weight-bolder position-relative">Admin Panel</h4>
                <p class="text-white position-relative">Kelola website kamu dari satu tempat.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <script src="{{ asset('admin/js/core/popper.min.js') }}"></script>
  <script src="{{ asset('admin/js/core/bootstrap.min.js') }}"></script>
  <script src="{{ asset('admin/js/argon-dashboard.min.js') }}"></script>
</body>
</html>

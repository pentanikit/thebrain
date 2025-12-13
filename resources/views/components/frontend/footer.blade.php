<footer class="footer-wrap position-relative overflow-hidden bg-white pt-5 pb-4">
  <!-- Wave background -->
  <div class="footer-wave position-absolute top-0 start-0 w-100">
    <svg viewBox="0 0 1440 180" preserveAspectRatio="none" class="w-100 d-block">
      <path d="M0,90 C240,160 480,20 720,90 C960,160 1200,20 1440,90 L1440,0 L0,0 Z"
            fill="#f5f5f5"></path>
    </svg>
  </div>

  <!-- Centered logo -->
  <div class="footer-logo position-absolute top-20 start-50 translate-middle">
    <img src="{{ asset('The-Brain-Logo.png') }}" alt="The Brain" class="img-fluid">
  </div>

  <!-- Content -->
  <div class="container footer-content">
    <div class="row g-4 text-center text-md-start justify-content-between">
      <div class="col-12 col-md-2">
        <h6 class="fw-bold mb-3">Know Us only</h6>
        <ul class="list-unstyled mb-0">
          <li class="mb-2"><a class="text-decoration-none text-dark" href="{{ route('homeRoute') }}">Home</a></li>
          <li class="mb-2"><a class="text-decoration-none text-dark" href="#">About Us</a></li>
          <li class="mb-2"><a class="text-decoration-none text-dark" href="#">Blog</a></li>
        </ul>
      </div>

      <div class="col-12 col-md-2">
        <h6 class="fw-bold mb-3">Customer Service</h6>
        <ul class="list-unstyled mb-0">
          <li class="mb-2"><a class="text-decoration-none text-dark" href="#">Privacy Policy</a></li>
          <li class="mb-2"><a class="text-decoration-none text-dark" href="#">Terms And Conditions</a></li>
          <li class="mb-2"><a class="text-decoration-none text-dark" href="#">Contact Us</a></li>
        </ul>
      </div>



      <div class="col-12 col-md-2">
        <h6 class="fw-bold mb-3">Head Office</h6>
        <p class="mb-2 small text-dark">
          The Brain 46, Shewrapara, 3rd Floor (4A) Mirpur Dhaka-1216
        </p>
        <p class="mb-3 small text-dark">+8801827400100</p>

        <p class="fw-bold mb-2">Follow The Brain</p>
        <div class="d-flex justify-content-center justify-content-md-start gap-2">
          <a href="https://facebook.com/thebrainlifestyle" class="social-btn"><i class="bi bi-facebook"></i></a>
          <a href="#" class="social-btn"><i class="bi bi-youtube"></i></a>
          <a href="#" class="social-btn"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
    </div>
  </div>
</footer>

<style>
  .footer-wrap { min-height: 320px; }

  /* push content down so logo doesn't overlap */
  .footer-content { padding-top: 70px; }

  .footer-logo{
    width: 110px;
    height: 110px;
    background: #fff;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,.12);
    z-index: 5;
  }

  .footer-wave { z-index: 1; }
  .footer-content { position: relative; z-index: 2; }

  .social-btn{
    width: 38px;
    height: 38px;
    border-radius: 999px;
    background: #e9ecef;
    color: #111;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: .2s ease;
  }
  .social-btn:hover{
    transform: translateY(-2px);
    background: #dee2e6;
  }
</style>
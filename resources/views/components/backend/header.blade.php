        <header class="main-header navbar">
            <div class="col-search">

            </div>
            <div class="col-nav">
                <button class="btn btn-icon btn-mobile me-auto" data-trigger="#offcanvas_aside"><i
                        class="material-icons md-apps"></i></button>
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link btn-icon" href="#"><i class="material-icons md-notifications animation-shake"></i><span class="badge rounded-pill">3</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-icon darkmode" href="#"><i class="material-icons md-nights_stay"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="requestfullscreen nav-link btn-icon" href="#"><i class="material-icons md-cast"></i></a>
                    </li>

                    <li class="dropdown nav-item"><a class="dropdown-toggle" id="dropdownAccount"
                            data-bs-toggle="dropdown" href="#" aria-expanded="false"><i class="material-icons md-account_circle"></i></a>  
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownAccount">

                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"><i class="material-icons md-exit_to_app"></i>Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </header>

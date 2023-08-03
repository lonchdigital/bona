<div>
    <a href="#" rel="noffolow" class="user-profile-link" aria-label="{{ trans('base.user') }}">
        <svg>
            <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-person"></use>
        </svg>
    </a>
    <div class="sub-menu bg-white">
        <div class="sub-menu-title py-3 text-center font-weight-bold text-uppercase">
            {{ trans('auth.user_profile') }}
        </div>
        <ul class="sub-menu-list list-unstyled mb-0 px-1 pt-1 user-profile-sub-menu">
            <li class="sub-menu-list-item">
                <div class="d-flex align-items-center bg-light p-2">
                    <svg>
                        <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-person"></use>
                    </svg>
                    <div class="pl-3">
                        <p class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</p>
                        <span>{{ $user->email }}</span>
                    </div>
                </div>
            </li>
            @if($user->isAdmin())
                <li class="sub-menu-list-item">
                    <div class="w-100 py-1">
                        <a class="btn btn-dark btn-block" href="{{ route('admin.dashboard.page') }}">{{ trans('user-profile.admin_panel') }}</a>
                    </div>
                </li>
            @endif
            <li class="sub-menu-list-item">
                <div class="w-100 py-1">
                    <a class="btn btn-dark btn-block" href="{{ route('profile.edit.page') }}">{{ trans('auth.edit_profile') }}</a>
                </div>
            </li>
            <li class="sub-menu-list-item">
                <div class="w-100 pb-1">
                    <form action="{{ route('auth.logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-go-to-cart btn-block">
                            <span>{{ trans('auth.logout') }}</span>
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</div>

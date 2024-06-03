<a href="@if($user->isAdmin()) {{ route('admin.order.list.page') }} @else {{ route('user.profile.orders.page') }} @endif" rel="noffolow" class="user-profile-link" aria-label="{{ trans('base.user') }}">
    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             viewBox="0 0 32 32" style="enable-background:new 0 0 32 32;" xml:space="preserve">
        <g>
            <g>
                <path d="M16,14c-3.8598633,0-7-3.140625-7-7s3.1401367-7,7-7s7,3.140625,7,7S19.8598633,14,16,14z M16,2
                    c-2.7568359,0-5,2.2431641-5,5s2.2431641,5,5,5s5-2.2431641,5-5S18.7568359,2,16,2z"/>
            </g>
            <g>
                <path d="M27,32c-0.5522461,0-1-0.4472656-1-1v-6.1152344c0-3.828125-3.1142578-6.9423828-6.9423828-6.9423828h-6.1152344
                    C9.1142578,17.9423828,6,21.0566406,6,24.8847656V31c0,0.5527344-0.4477539,1-1,1s-1-0.4472656-1-1v-6.1152344
                    c0-4.9306641,4.0117188-8.9423828,8.9423828-8.9423828h6.1152344C23.9882813,15.9423828,28,19.9541016,28,24.8847656V31
                    C28,31.5527344,27.5522461,32,27,32z"/>
            </g>
        </g>
    </svg>
</a>

<div class="main-user-navigation">
    <ul>
        <li @if(Route::currentRouteName() === 'user.profile.orders.page') class="active" @endif><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('user.profile.orders.page') }}">{{ trans('user-profile.user_orders') }}</a></li>
        <li @if(Route::currentRouteName() === 'profile.edit.page') class="active" @endif><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('profile.edit.page') }}">{{ trans('user-profile.update_user_data') }}</a></li>
        <li @if(Route::currentRouteName() === 'profile.edit-password.page') class="active" @endif><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('profile.edit-password.page') }}">{{ trans('user-profile.update_password') }}</a></li>
        <li class="sub-menu-list-item">
            <form action="<?php echo e(route('auth.logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button class="btn btn-go-to-cart btn-block">
                    <span><?php echo e(trans('auth.logout')); ?></span>
                </button>
            </form>
        </li>
    </ul>
</div>

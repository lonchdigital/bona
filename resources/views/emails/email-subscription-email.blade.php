@extends('layouts.email-main')

@section('content')
    <!-- START CENTERED WHITE CONTAINER -->
    <table role="presentation" class="main">

        <!-- START MAIN CONTENT AREA -->
        <tr>
            <td class="wrapper">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <p class="align-center">
                                <img class="align-center" src="{{ Vite::asset('resources/img/logo.svg') }}" alt="logo">
                            </p>
                            <h2 class="align-center">{{ trans('emails.email-subscription-email.subject') }}</h2>
                            <p class="align-center">{{ trans('emails.email-subscription-email.main_text') }}</p>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                <tbody>
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tbody>
                                            <tr>
                                                <td> <a href="{{ route('email-subscription.confirm', ['emailSubscriptionCode' => $code]) }}" target="_blank">{{ trans('emails.email-subscription-email.call_to_action') }}</a> </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <p class="align-center">{{ trans('emails.email-subscription-email.support_text') }}</p>
                            <p class="align-center"><a href="{{ route('email-subscription.confirm', ['emailSubscriptionCode' => $code]) }}">{{ route('email-subscription.confirm', ['emailSubscriptionCode' => $code]) }}</a></p>

                            <p class="mt-20">{{ trans('emails.email_footer_text') }}</p>
                            <p>{{ trans('emails.email_footer_text_2', ['SITE_NAME' => config('app.name')]) }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- END MAIN CONTENT AREA -->
    </table>
    <!-- END CENTERED WHITE CONTAINER -->
@endsection

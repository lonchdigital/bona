@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')

    <!-- ========================  Header content ======================== -->

    <section class="header-content">

        <div class="owl-slider">

            @foreach($slides as $slide)
                <div class="item" style="background-image:url({{ $slide->slide_image_url }})">
                    <div class="box">
                        <div class="container">
                            <h2 class="title animated h1" data-animation="fadeInDown">{{ $slide->title }}</h2>
                            <div class="slider-description animated font-two" data-animation="fadeInUp">{{ $slide->description }}</div>
                            <div class="animated" data-animation="fadeInUp">
                                <a href="{{ $slide->button_url }}" target="_blank" class="btn btn-empty" >{{ $slide->button_text }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div> <!--/owl-slider-->
    </section>

    <!-- ========================  Icons slider ======================== -->

    <section class="owl-icons-wrapper owl-icons-frontpage">

        <div class="container">

            <div class="owl-icons">

                <!-- === icon item === -->
                <a href="#">
                    <figure>
                        <div class="icon-wrapper">
                            <svg width="54" height="54" viewBox="0 0 54 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="15.4297" width="25.0714" height="52.4872" fill="white"/>
                                <path d="M21.9222 28.6881H20.2656V31.2364H17.7035V24.4517H20.2656V25.311H21.9222V22.7942H16.0469V32.894H21.9222V28.6881Z" fill="black"/>
                                <path d="M24.4688 26.1711H18.5625V27.8277H24.4688V26.1711Z" fill="black"/>
                                <path d="M10.9535 5.90942H2.54688V19.3863H10.9535V5.90942ZM9.29689 17.7287H4.20337V7.56697H9.29678L9.29689 17.7287Z" fill="white"/>
                                <path d="M8.43751 15.196H5.0625V16.8526H8.43751V15.196Z" fill="white"/>
                                <path d="M7.57848 9.271H5.92188V13.4922H7.57848V9.271Z" fill="white"/>
                                <path d="M5.0625 20.2615H3.375V21.9181H5.0625V20.2615Z" fill="white"/>
                                <path d="M7.59375 20.2615H5.90625V21.9181H7.59375V20.2615Z" fill="white"/>
                                <path d="M10.125 20.2615H8.4375V21.9181H10.125V20.2615Z" fill="white"/>
                                <path d="M54 49.7866V48.1291H41.3282V0H12.6717V48.1292H0V49.7867H12.6717V52.3424H0V54H54V52.3424H41.3282V49.7866H54ZM39.6716 52.3422H14.3283V1.65744H39.6717L39.6716 52.3422Z" fill="white"/>
                            </svg>
                        </div>
                        <figcaption>Білі двері</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->
                <a href="#">
                    <figure>
                        <div class="icon-wrapper">
                            <svg width="54" height="54" viewBox="0 0 54 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M30.6214 9.53782L30.6245 9.53467C31.0308 9.12671 31.5137 8.80301 32.0454 8.58213C32.5771 8.36125 33.1472 8.24755 33.723 8.24755C34.2988 8.24755 34.8689 8.36125 35.4006 8.58213C35.9323 8.80301 36.4152 9.12671 36.8215 9.53467L36.8229 9.53611L44.4618 17.175C44.4622 17.1754 44.4626 17.1758 44.463 17.1762C45.2817 18.0005 45.7413 19.1152 45.7413 20.277C45.7413 21.4389 45.2817 22.5536 44.4629 23.3779C44.4625 23.3783 44.4622 23.3786 44.4618 23.379L25.3 42.5656V14.8336L30.6214 9.53782ZM25.3 53V49.4308L25.3099 49.1933L41.1032 33.4H48.627C51.0415 33.4 53 35.352 53 37.8V48.6C53 49.767 52.5364 50.8861 51.7113 51.7113C50.8861 52.5364 49.767 53 48.6 53H25.3ZM1 5.373C1 2.98228 2.95528 1 5.4 1H16.2C17.367 1 18.4861 1.46357 19.3113 2.28873C20.1364 3.11389 20.6 4.23305 20.6 5.4V48.6C20.6 49.767 20.1364 50.8861 19.3113 51.7113C18.4861 52.5364 17.367 53 16.2 53H5.4C4.23305 53 3.11389 52.5364 2.28873 51.7113C1.46357 50.8861 1 49.767 1 48.6V5.4V5.373ZM10.8 46.9C11.7813 46.9 12.7224 46.5102 13.4163 45.8163C14.1102 45.1224 14.5 44.1813 14.5 43.2C14.5 42.2187 14.1102 41.2776 13.4163 40.5837C12.7224 39.8898 11.7813 39.5 10.8 39.5C9.8187 39.5 8.87759 39.8898 8.18371 40.5837C7.48982 41.2776 7.1 42.2187 7.1 43.2C7.1 44.1813 7.48982 45.1224 8.18371 45.8163C8.87759 46.5102 9.8187 46.9 10.8 46.9Z" stroke="white" stroke-width="2"/>
                            </svg>
                        </div>
                        <figcaption>ФАРБОВАні двері</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->
                <a href="#">
                    <figure>
                        <div class="icon-wrapper">
                            <svg width="56" height="54" viewBox="0 0 56 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.07905 0.186609L2.79914 0.443196V24.9356V49.3814L1.65616 49.4281C0.559827 49.4514 0.513175 49.4747 0.256588 49.7546C8.34208e-08 50.0346 0 50.1279 0 51.714V53.3935L0.30324 53.6968L0.60648 54H27.5482H54.4432L54.7464 53.6968L55.0497 53.3935V51.714C55.0497 50.1045 55.0497 50.0346 54.7931 49.7546C54.5365 49.4747 54.4898 49.4514 53.3935 49.4281L52.2739 49.3814V48.9616C52.2739 48.6583 52.3205 48.5417 52.4371 48.5417C52.5305 48.5417 52.7404 48.4017 52.927 48.2384L53.2302 47.9352V46.2557V44.5762L52.927 44.273L52.6238 43.9698H50.6877H48.7516V41.0773V38.1849L49.3814 37.7883C50.1745 37.2985 51.0376 36.3888 51.4575 35.5257C51.7374 34.9659 51.8073 34.7326 51.854 33.8462C51.8773 33.0065 51.854 32.7032 51.6674 32.1667C51.5274 31.7935 51.4341 31.397 51.4341 31.2803C51.4341 31.1404 51.6207 30.7205 51.8773 30.324C53.0203 28.3879 53.1603 26.3119 52.1806 25.0989C51.994 24.8657 51.9706 24.7257 52.0406 24.0492C52.1339 22.9762 51.9007 22.2531 51.2475 21.53C50.2678 20.4337 48.565 19.9438 47.2587 20.387C46.3724 20.6903 44.8562 21.9965 44.413 22.8363C44.133 23.3961 44.0164 24.7024 44.1797 25.3322C44.2963 25.7287 44.273 25.822 43.8065 26.7317C42.9434 28.5279 42.9201 30.0207 43.7598 31.7002C44.1564 32.4933 44.1797 32.5866 44.0864 32.9832C43.9231 33.6363 43.9464 34.3594 44.203 35.5024C44.4596 36.7387 44.6929 37.3451 45.1361 37.8583C45.4626 38.2549 46.1391 38.6047 46.559 38.6281H46.7922V41.3106V43.9931H44.9028C42.9434 43.9931 42.5002 44.0864 42.3369 44.4829C42.3136 44.5762 42.267 45.416 42.267 46.349C42.267 48.1685 42.3369 48.4251 42.8501 48.565C43.0834 48.6117 43.13 48.705 43.13 49.0549V49.4747H41.7305H40.3309V24.9823V0.489849L40.051 0.233261L39.7711 0H21.5533H3.35896L3.07905 0.186609ZM36.8553 2.21598L36.4354 2.63585H21.5767H6.74125L6.39136 2.26263C6.20475 2.07603 6.04147 1.86609 6.04147 1.84276C6.04147 1.81944 13.0626 1.77279 21.6467 1.77279H37.2518L36.8553 2.21598ZM5.50497 28.0847V52.2039H5.0851H4.66523V27.6648V3.1257L5.0851 3.54557L5.50497 3.96544V28.0847ZM38.5114 27.6881V52.2039H38.0916H37.6717V28.0847V3.98877L38.0683 3.5689C38.2782 3.33564 38.4881 3.14903 38.4881 3.14903C38.5114 3.17235 38.5114 14.2056 38.5114 27.6881ZM35.8289 28.3879V52.2039L21.6233 52.1806L7.39438 52.1572L7.37106 28.3646L7.34773 4.57192H21.6H35.8523V28.3879H35.8289ZM49.3114 22.2065C50.0579 22.5797 50.1745 22.8596 50.1978 24.0959C50.1978 25.1922 50.3145 25.5654 50.781 26.2419C51.2009 26.895 50.9676 28.2246 50.1978 29.5076C49.778 30.2073 49.5447 31.0704 49.638 31.6536C49.6613 31.8635 49.8013 32.3533 49.9179 32.7499C50.2678 33.8695 49.9646 34.8492 49.0082 35.7356C48.2384 36.4354 47.0721 36.9486 46.6756 36.7387C46.1624 36.4587 45.8825 35.2458 45.8825 33.1931V31.6302L45.4626 30.8371C45.0661 30.0907 45.0428 29.9741 45.0428 29.181C45.0428 28.3646 45.0661 28.2479 45.5326 27.3849L46.0225 26.4752V25.0756C46.0225 23.5361 46.0691 23.3495 46.6289 22.9762C46.8156 22.8363 47.1654 22.5564 47.3987 22.3698C47.7486 22.0665 47.8652 22.0199 48.3551 22.0199C48.6583 21.9965 49.0315 22.0665 49.3114 22.2065ZM51.3875 46.2091L51.3641 46.6523L47.7486 46.6756L44.1097 46.6989V46.2091V45.7425H47.7719H51.4341L51.3875 46.2091ZM50.4544 50.3844V52.2039H47.7019H44.9495V50.3844V48.565H47.7019H50.4544V50.3844ZM2.70583 51.714V52.2039H2.28596H1.86609V51.714V51.2242H2.28596H2.70583V51.714ZM43.13 51.714V52.2039H41.7771H40.4242V51.714V51.2242H41.7771H43.13V51.714ZM53.2302 51.7374L53.2536 52.2039H52.8337H52.3905V51.714V51.2242L52.8104 51.2475C53.1836 51.2942 53.1836 51.2942 53.2302 51.7374Z" fill="white"/>
                                <path d="M9.47143 6.67127L9.21484 6.95118V16.4449V25.9387L9.47143 26.2186L9.72802 26.4985H14.7198H19.6883L19.9682 26.1719L20.2481 25.8454V16.4916C20.2481 9.58704 20.2248 7.06782 20.1081 6.90453C19.8282 6.39136 19.9215 6.41468 14.6965 6.39136H9.72802L9.47143 6.67127ZM18.4053 16.4449V24.679H14.6965H10.9876V16.4449V8.23412H14.6965H18.4053V16.4449Z" fill="white"/>
                                <path d="M23.2096 6.74125L22.9297 7.06781L22.953 16.5149L22.9763 25.962L23.2329 26.2419L23.4895 26.5218H28.4813H33.4498L33.7297 26.1952L34.0096 25.8687V16.4916C34.0096 9.58704 33.9863 7.06782 33.8696 6.90453C33.5897 6.39136 33.683 6.41468 28.4347 6.39136H23.4662L23.2096 6.74125ZM32.1668 16.4449V24.679H28.458H24.7491V16.4449V8.23412H28.458H32.1668V16.4449Z" fill="white"/>
                                <path d="M30.0444 27.4781C29.2279 27.7347 29.1113 28.761 29.8811 29.1576C30.021 29.2509 30.8374 29.2975 32.2137 29.2975C34.313 29.2975 34.313 29.2975 34.5696 29.0176C34.9662 28.6211 34.9662 28.0845 34.5696 27.6647C34.313 27.3848 34.2897 27.3848 32.3536 27.3848C31.2806 27.3848 30.2543 27.4081 30.0444 27.4781Z" fill="white"/>
                                <path d="M9.68174 30.3239C9.54179 30.4172 9.35518 30.6038 9.26187 30.7671C9.16857 31.0003 9.14524 33.5429 9.16857 40.4474L9.1919 49.8478L9.49513 50.1044L9.79838 50.361H14.6735H19.5487L19.8519 50.1044L20.1552 49.8478V40.2841V30.7204L19.8986 30.4638L19.642 30.2072L14.7902 30.1839C10.7781 30.1606 9.93833 30.1839 9.68174 30.3239ZM18.3591 40.2608V48.4949H14.6969H11.0347L11.0113 40.3774C11.0113 35.9221 11.0113 32.19 11.0347 32.12C11.058 32.0033 11.8511 31.98 14.7202 32.0033L18.3591 32.0267V40.2608Z" fill="white"/>
                                <path d="M23.4678 30.3005C22.908 30.5805 22.9313 30.3005 22.9546 40.4008L22.978 49.8478L23.2812 50.1044L23.5844 50.361H28.4596H33.3348L33.638 50.1044L33.9412 49.8478V40.2841V30.7204L33.6847 30.4638L33.4281 30.2072L28.5762 30.1839C24.8207 30.1606 23.6778 30.1839 23.4678 30.3005ZM32.1218 40.2608V48.4949H28.4596H24.7974L24.7741 40.3774C24.7741 35.9221 24.7741 32.19 24.7974 32.12C24.8207 32.0033 25.6138 31.98 28.4829 32.0033L32.1218 32.0267V40.2608Z" fill="white"/>
                            </svg>
                        </div>
                        <figcaption>вуличні двері</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->
                <a href="#">
                    <figure>
                        <div class="icon-wrapper">
                            <svg width="30" height="54" viewBox="0 0 30 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M29.3534 49.7866V48.1291V0H0.680446V48.1292V49.7867V52.3424L0.679688 54H29.3534V52.3424V49.7866ZM27.6959 52.3422H2.33799V1.65744H27.696L27.6959 52.3422Z" fill="white"/>
                                <path d="M21.771 23.5419H7.87733C6.91809 23.5419 6.14062 22.7549 6.14062 21.7853V13.5875C6.14062 12.6172 6.91809 11.8308 7.87733 11.8308H9.03514L13.9518 8.01414C13.9298 7.92338 13.9147 7.82969 13.9147 7.7319C13.9147 7.08545 14.4334 6.56079 15.0725 6.56079C15.7116 6.56079 16.2297 7.08545 16.2297 7.7319C16.2297 7.94212 16.1707 8.13711 16.074 8.30809L20.6132 11.8308H21.771C22.7297 11.8308 23.5077 12.6172 23.5077 13.5875V21.7853C23.5077 22.7549 22.7297 23.5419 21.771 23.5419ZM15.4442 19.7844H17.7777V19.3406H15.9368V16.0099H15.4436L15.4442 19.7844ZM12.1051 19.7844L12.5138 18.6408H14.0814L14.511 19.7844H15.0737L13.5523 16.0105H13.0174L11.5829 19.7844H12.1051ZM8.65422 16.4532C8.5477 16.6195 8.49444 16.7981 8.49444 16.9884C8.49444 17.1623 8.53786 17.3186 8.62528 17.4592C8.71269 17.5997 8.84526 17.7162 9.02298 17.8117C9.1596 17.8855 9.39869 17.9639 9.74024 18.0471C10.0818 18.1302 10.3024 18.1917 10.4025 18.2309C10.5582 18.2918 10.67 18.365 10.7388 18.4534C10.8077 18.5419 10.8425 18.6443 10.8425 18.7626C10.8425 18.8786 10.8072 18.9869 10.7371 19.0858C10.6671 19.1848 10.56 19.2627 10.4158 19.3195C10.2723 19.3769 10.1067 19.405 9.91912 19.405C9.70782 19.405 9.51794 19.3681 9.3489 19.2926C9.17929 19.2182 9.05482 19.1198 8.97493 18.998C8.89504 18.8762 8.84294 18.7205 8.82095 18.5313L8.35493 18.5723C8.36187 18.8247 8.43019 19.0501 8.55986 19.2498C8.68953 19.4495 8.86784 19.6 9.09534 19.6995C9.32343 19.7991 9.60536 19.8488 9.94228 19.8488C10.2074 19.8488 10.4465 19.7996 10.659 19.7007C10.8714 19.6023 11.0341 19.4653 11.1476 19.2873C11.261 19.1093 11.3178 18.9213 11.3178 18.7205C11.3178 18.5184 11.2657 18.3404 11.162 18.1853C11.0584 18.0301 10.898 17.9019 10.6804 17.8011C10.5304 17.7326 10.256 17.6524 9.8566 17.5611C9.45658 17.4691 9.20939 17.379 9.11503 17.2911C9.01835 17.2045 8.9703 17.0926 8.9703 16.9574C8.9703 16.8004 9.03861 16.6675 9.17465 16.5568C9.3107 16.4462 9.52721 16.3905 9.82476 16.3905C10.1107 16.3905 10.3267 16.4514 10.4726 16.5721C10.619 16.6933 10.7041 16.8724 10.7296 17.109L11.2043 17.0727C11.1956 16.8514 11.1348 16.6534 11.0219 16.479C10.909 16.3045 10.7464 16.1721 10.5357 16.0819C10.3249 15.9918 10.0818 15.9473 9.80566 15.9473C9.55499 15.9473 9.32748 15.99 9.12255 16.0761C8.91646 16.1616 8.76074 16.2869 8.65422 16.4532ZM15.7956 8.63952C15.5964 8.80172 15.3475 8.90302 15.0725 8.90302C14.6893 8.90302 14.3524 8.71271 14.1416 8.42228L9.61404 11.8308H20.0343L15.7956 8.63952ZM21.155 19.3406H18.8666V18.0553H20.9287V17.6114H18.8666V16.4538H21.0694V16.0099H18.374V19.7838H21.1556L21.155 19.3406ZM13.2692 16.404C13.3294 16.6107 13.4174 16.8724 13.5338 17.1904L13.9269 18.2339H12.6539L13.0678 17.1289C13.1529 16.8906 13.22 16.6488 13.2692 16.404Z" fill="white"/>
                            </svg>
                        </div>
                        <figcaption>Акційні пропозиції</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->
                <a href="#">
                    <figure>
                        <div class="icon-wrapper">
                            <svg width="30" height="54" viewBox="0 0 93 175" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M37 95H18V100H37V95Z" fill="white"/>
                                <path d="M29 102.674H23.6428V110.748H15.3572V89.2518H23.6428V91.9741H29V84H10V116H29V102.674Z" fill="white"/>
                                <path d="M93 161.346V155.974V0H0.00245904V155.974V161.346V169.628L0 175H93V169.628V161.346ZM87.6239 169.628H5.37853V5.37135H87.6243L87.6239 169.628Z" fill="white"/>
                                <ellipse cx="69.3184" cy="23.3184" rx="15.5067" ry="0.5" transform="rotate(45 69.3184 23.3184)" fill="white"/>
                                <ellipse cx="75.1688" cy="17.1674" rx="6.88613" ry="0.421624" transform="rotate(45 75.1688 17.1674)" fill="white"/>
                            </svg>
                        </div>
                        <figcaption>Дзеркальні двері</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->
                <a href="#">
                    <figure>
                        <div class="icon-wrapper">
                            <svg width="48" height="54" viewBox="0 0 48 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.9839 23.4292C17.9839 22.8798 18.4278 22.4359 18.9771 22.4359C19.5265 22.4359 19.9704 22.8798 19.9704 23.4292V44.0148C19.9704 46.7616 18.8453 49.2579 17.039 51.0686C15.2283 52.8793 12.732 54 9.98519 54C7.23838 54 4.74208 52.8749 2.93139 51.0686C1.12509 49.2623 0 46.766 0 44.0192V9.98519C0 7.23838 1.12509 4.74208 2.93139 2.93139C4.74208 1.12509 7.23838 0 9.98519 0C11.7607 0 13.4352 0.474648 14.8899 1.30089C16.3929 2.1535 17.6543 3.38846 18.5464 4.86954C18.8277 5.33979 18.6739 5.95068 18.2036 6.23195C17.7334 6.51323 17.1225 6.3594 16.8412 5.88915C16.1292 4.70253 15.114 3.71368 13.9054 3.02808C12.7452 2.36884 11.4047 1.99528 9.98079 1.99528C7.78335 1.99528 5.78367 2.89623 4.33336 4.34655C2.88305 5.79686 1.9821 7.79653 1.9821 9.99398V44.028C1.9821 46.2254 2.88305 48.2251 4.33336 49.6754C5.78367 51.1257 7.78335 52.0267 9.98079 52.0267C12.1782 52.0267 14.1779 51.1257 15.6282 49.6754C17.0785 48.2251 17.9795 46.2254 17.9795 44.028V23.4292H17.9839ZM12.7056 8.90844H41.6196C43.2018 8.90844 44.6345 9.55449 45.6761 10.5961C46.7177 11.6377 47.3637 13.0704 47.3637 14.6526C47.3637 16.2347 46.7177 17.6675 45.6761 18.709C44.6345 19.7506 43.2018 20.3967 41.6196 20.3967H12.7056C11.1235 20.3967 9.69073 19.7506 8.64914 18.709C7.60755 17.6675 6.9615 16.2347 6.9615 14.6526C6.9615 13.0704 7.60755 11.6377 8.64914 10.5961C9.69073 9.55449 11.1279 8.90844 12.7056 8.90844ZM41.6196 10.8993H12.7056C11.6728 10.8993 10.7367 11.3212 10.0555 12.0024C9.3743 12.6836 8.95239 13.6198 8.95239 14.6526C8.95239 15.6854 9.3743 16.6215 10.0555 17.3027C10.7367 17.9839 11.6728 18.4058 12.7056 18.4058H41.6196C42.6524 18.4058 43.5885 17.9839 44.2697 17.3027C44.9509 16.6215 45.3728 15.6854 45.3728 14.6526C45.3728 13.6198 44.9509 12.6836 44.2697 12.0024C43.5885 11.3212 42.6524 10.8993 41.6196 10.8993ZM11.0663 40.6879V44.5378C11.0663 45.0872 10.6224 45.531 10.0731 45.531C9.52372 45.531 9.07984 45.0872 9.07984 44.5378V40.6395C8.56564 40.4681 8.10418 40.1825 7.73061 39.8089C7.11093 39.1892 6.72418 38.3278 6.72418 37.3829C6.72418 36.4336 7.10654 35.5766 7.73061 34.9569C8.35029 34.3373 9.21169 33.9505 10.1566 33.9505C11.1059 33.9505 11.9629 34.3329 12.5826 34.9569C13.2022 35.5766 13.589 36.438 13.589 37.3829C13.589 38.3322 13.2066 39.1892 12.5826 39.8089C12.1651 40.222 11.6465 40.5297 11.0663 40.6879ZM11.1718 36.3589C10.9125 36.0996 10.5521 35.937 10.1522 35.937C9.75226 35.937 9.39188 36.0996 9.13258 36.3589C8.87328 36.6182 8.71067 36.9786 8.71067 37.3785C8.71067 37.7785 8.87328 38.1388 9.13258 38.3981C9.39188 38.6574 9.75226 38.8201 10.1522 38.8201C10.5521 38.8201 10.9125 38.6574 11.1718 38.3981C11.4311 38.1388 11.5937 37.7785 11.5937 37.3785C11.5981 36.983 11.4355 36.6226 11.1718 36.3589Z" fill="white"/>
                            </svg>
                        </div>
                        <figcaption>ручки</figcaption>
                    </figure>
                </a>


            </div> <!--/owl-icons-->
        </div> <!--/container-->
    </section>

    <!-- ========================  Products Category widget ======================== -->

    <section class="art-products-category">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="title h2">{{trans('base.doors_by_type')}}</h2>
                        <div class="subtitle font-two">
                            <p>{{trans('base.doors_category')}}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="art-category-list">
                @foreach($productTypes as $productType)
                    <div class="art-category-item">
                        <article>
                            <div class="figure-grid">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">
                                    <div class="image">
                                        <img src="{{ $productType->image_url }}" alt="Product Type Image">
                                    </div>
                                    <div class="text">
                                        <h4 class="title">{{ $productType->name }}</h4>
                                    </div>
                                </a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    @if(count($homeNewProducts))
        <!-- ======================== New Products  ======================== -->
        <section class="products">
            <div class="container">

                <div class="art-products-slider-wrapper">
                    <div class="art-products-owl-items">
                        @foreach($homeNewProducts as $product)
                            <div class="item">

                                <div class="art-product-item">
                                    <div class="art-product-data">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->product->slug]) }}" class="">
                                            <div class="image">
                                                <img src="{{ $product->product->preview_image_url }}" alt="">
                                            </div>
                                            <div class="text">
                                                <h2 class="product-title">{{ $product->product->name }}</h2>
                                                <span class="price-wrapper">
                                                <span class="price">{{ $product->product->price }}</span>
                                                <span class="currency">{{ $baseCurrency->name_short }}</span>
                                            </span>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div> <!--/row-->

            </div> <!--/container-->
        </section>
    @endif


    <x-precise-form-component />

    @if(count($homeBestSalesProducts))
        <!-- ======================== Best Sales Products  ======================== -->

        <section class="products">

            <div class="container">

                <header>
                    <div class="row">
                        <div class="col-12 text-center">
                            <h2 class="title h2">{{trans('base.best_sales')}}</h2>
                            <div class="subtitle font-two">
                                <p>Check out our latest collections</p>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="art-products-slider-wrapper">
                    <div class="art-products-owl-items">
                        @foreach($homeBestSalesProducts as $product)
                            <div class="item">

                                <div class="art-product-item">
                                    <div class="art-product-data">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->product->slug]) }}" class="">
                                            <div class="image">
                                                <img src="{{ $product->product->preview_image_url }}" alt="">
                                            </div>
                                            <div class="text">
                                                <h2 class="product-title">{{ $product->product->name }}</h2>
                                                <span class="price-wrapper">
                                                <span class="price">{{ $product->product->price }}</span>
                                                <span class="currency">{{ $baseCurrency->name_short }}</span>
                                            </span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div> <!--/row-->
            </div> <!--/container-->

        </section>
    @endif

    <!-- ========================  Instagram ======================== -->

    <section class="instagram">
        <header>
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="title h2">Follow us <i class="fa fa-instagram fa-2x"></i> Instagram </h2>
                    <div class="subtitle font-two">
                        <p>@InstaFurnitureFactory</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- === instagram gallery === -->

{{--        @dd($instagramFeed)--}}

        <div class="gallery clearfix">
            <a class="item" href="#">
                <img src="assets/images/square-1.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-2.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-3.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-4.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-5.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-6.jpg" alt="Alternate Text" />
            </a>

        </div> <!--/gallery-->

    </section>

    <!-- ========================  Blog ======================== -->

    <section class="blog">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="title h2">{{trans('base.blog')}}</h2>
                        <div class="subtitle font-two">
                            <p>{{trans('base.blog_latest')}}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="row art-blog-wrapper">
                @foreach($articles as $article)
                    <div class="col-md-6 col-lg-4">
                        <article class="art-post-archive-item">
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]) }}">
                                <div class="image" style="background-image:url({{ $article->hero_image_url }})">
                                    <img src="{{ $article->hero_image_url }}" alt="">
                                </div>
                                <div class="entry entry-post">
                                    <div class="preview-post-left">
                                        <div class="date-wrapper">
                                            <div class="date">
                                                <strong>{{ $article->created_at->format('d') }}</strong>
                                                <span>{{ $article->created_at->format('M') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-post-right">
                                        <div class="title">
                                            <h2 class="h5">{{ $article->name }}</h2>
                                        </div>
                                        <div class="art-preview-text"><p>{{ $article->preview_text }}</p></div>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>
                @endforeach
            </div> <!--/row-->

            <div class="wrapper-more">
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}" class="btn btn-empty color-dark">{{trans('base.blog_all')}}</a>
            </div>

        </div> <!--/container-->
    </section>

    <!-- ======================== Quotes ======================== -->

    <section class="quotes quotes-slider" style="background-image:url({{ asset('storage/bg-images/testimonials-bg.png') }})">
        <div class="container">

            <!-- === quotes header === -->
            <header>
                <h2 class="title h2">{{ trans('base.what_clients_say') }}</h2>
            </header>

            <div class="row">

                <div class="quote-carousel art-quote-carousel-home">

                    @foreach($homeTestimonials as $testimonial)
                        <div class="quote">
                            <div class="image">
                                <img src="{{ $testimonial->testimonial_image_url }}" alt="Testimonial image">
                            </div>
                            <div class="name">
                                <h4>{{ $testimonial->name }}</h4>
                            </div>
                            <div class="text">
                                <p>{{ $testimonial->review }}</p>
                            </div>
                        </div>
                    @endforeach

                </div> <!--/quote-carousel-->
            </div> <!--/row-->
        </div> <!--/container-->
    </section>

    <!-- ======================== FAQs ======================== -->

    <section class="faqs-section">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="title h2">{{ trans('base.faqs') }}</h2>
                        <div class="subtitle font-two">
                            <p>{{trans('base.faqs_subtitle')}}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="accordion-faqs">

                <div class="faq-col">
                    @foreach($faqs as $index => $faq)
                        @if($index % 2 == 0)
                            <div class="accordion-item-wrapper">
                                <button class="accordion">
                                    <span class="question">{{ $faq->question }}</span>
                                </button>
                                <div class="art-panel">
                                    <div class="panel-data">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="faq-col">
                    @foreach($faqs as $index => $faq)
                        @if($index % 2 != 0)
                            <div class="accordion-item-wrapper">
                                <button class="accordion">
                                    <span class="question">{{ $faq->question }}</span>
                                </button>
                                <div class="art-panel">
                                    <div class="panel-data">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>

        </div>
    </section>


    <!-- ======================== SEO ======================== -->
    <section class="seo-section">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="title h2">{{$seoText['title']}}</h2>
                    </div>
                </div>
            </header>

            <div class="seo-content">
                {!! $seoText['content'] !!}
            </div>

        </div>
    </section>


@stop

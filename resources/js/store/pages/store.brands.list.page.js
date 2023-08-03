import $ from 'jquery';

export default async function () {
    const [
        EmailSubscription,
    ] = await Promise.all([
        import('../common/email-subscription'),

    ]);

    EmailSubscription.default.init();
}

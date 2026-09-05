export const parseInstallmentRates = (value) => {
    try {
        return Object.fromEntries(
            Object.entries(JSON.parse(value || '{}'))
                .map(([period, rate]) => [Number.parseInt(period, 10), Number.parseFloat(rate)])
                .filter(([period, rate]) => Number.isInteger(period) && period > 0 && Number.isFinite(rate) && rate >= 0),
        );
    } catch {
        return {};
    }
};

export const installmentQuote = (baseAmount, period, rate) => {
    const safePeriod = Math.max(1, Number.parseInt(period, 10) || 1);
    const baseInCents = Math.max(0, Math.round((Number(baseAmount) || 0) * 100));
    const rateBasisPoints = Math.max(0, Math.round((Number(rate) || 0) * 100));
    const feeInCents = Math.round(baseInCents * rateBasisPoints / 10000);
    const totalInCents = baseInCents + feeInCents;

    return {
        rate: rateBasisPoints / 100,
        base: baseInCents / 100,
        fee: feeInCents / 100,
        total: totalInCents / 100,
        monthly: Math.ceil(totalInCents / safePeriod) / 100,
    };
};

export const formatInstallmentRate = (rate) => new Intl.NumberFormat(
    document.documentElement.lang || 'uk-UA',
    { maximumFractionDigits: 2 },
).format(Number(rate) || 0);

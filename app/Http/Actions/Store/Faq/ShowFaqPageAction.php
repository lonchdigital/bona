<?php

namespace App\Http\Actions\Store\Faq;

use App\Services\Faq\FaqService;

class ShowFaqPageAction
{
    public function __invoke(FaqService $faqService)
    {
        $groups = $faqService->getGroupedFaqs();

        return view('pages.store.faq-page', [
            'faqGroups' => $groups,
            'faqCount' => $faqService->countQuestions($groups),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_ThrowableInfo_StringConverter
{
    /**
     * @var ConvertHelper_ThrowableInfo
     */
    private $info;

    public function __construct(ConvertHelper_ThrowableInfo $info)
    {
        $this->info = $info;
    }

    public function toString() : string
    {
        return
            $this->renderMessage() .
            $this->renderCalls() .
            $this->renderPrevious();
    }

    /**
     * @return string
     */
    private function renderMessage() : string
    {
        $string = 'Exception';

        if ($this->info->hasCode())
        {
            $string .= ' #' . $this->info->getCode();
        }

        $string .=
            ': ' .
            $this->info->getMessage() .
            PHP_EOL;

        return $string;
    }

    /**
     * @return string
     */
    private function renderCalls() : string
    {
        $calls = $this->info->getCalls();

        $string = '';

        foreach ($calls as $call)
        {
            $string .= $call->toString() . PHP_EOL;
        }

        return $string;
    }

    /**
     * @return string
     * @throws ConvertHelper_Exception
     */
    private function renderPrevious() : string
    {
        if (!$this->info->hasPrevious())
        {
            return '';
        }

        return
            PHP_EOL .
            PHP_EOL .
            'Previous error:' .
            PHP_EOL .
            PHP_EOL .
            $this->info->getPrevious()->toString();
    }
}

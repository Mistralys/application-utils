<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_ThrowableInfo_MessageRenderer
{
    /**
     * @var ConvertHelper_ThrowableInfo
     */
    private $info;

    /**
     * @var bool
     */
    private $developerInfo;

    public function __construct(ConvertHelper_ThrowableInfo $info, bool $developerInfo)
    {
        $this->info = $info;
        $this->developerInfo = $developerInfo;
    }

    /**
     * @return string
     * @throws ConvertHelper_Exception
     */
    public function render() : string
    {
        $finalCall = $this->info->getFinalCall();

        $message = sb()
            ->t('A %1$s exception occurred.', $this->info->getClass())
            ->eol()
            ->t('Code:')
            ->add($this->info->getCode())
            ->t('Message:')
            ->add($this->info->getMessage());

        if($this->developerInfo)
        {
            $message
                ->eol()
                ->t('Final call:')
                ->add($finalCall->toString());
        }

        if($this->developerInfo && $this->info->hasDetails())
        {
            $message
                ->t('Developer details:')
                ->eol()
                ->add($this->info->getDetails());
        }

        if($this->info->hasPrevious())
        {
            $message
                ->eol()
                ->eol()
                ->t('Previous exception:')
                ->eol()
                ->add($this->info->getPrevious()->renderErrorMessage($this->developerInfo));
        }

        return (string)$message;
    }
}

<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\tasks;

use pocketmine\promise\PromiseResolver;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Filesystem;

final class AsyncCopyTask extends AsyncTask{
    /**
     * @param string $src
     * @param string $dst
     * @param PromiseResolver<true> $resolver
     */
    public function __construct(private readonly string $src, private readonly string $dst, PromiseResolver $resolver)
    {
        $this->storeLocal('resolver', $resolver);
    }

    public function onRun(): void
    {
        Filesystem::recursiveCopy($this->src, $this->dst);
    }

    public function onCompletion(): void
    {
        /** @var PromiseResolver<true> $resolver */
        $resolver = $this->fetchLocal('resolver');
        $resolver->resolve(true);
    }
}
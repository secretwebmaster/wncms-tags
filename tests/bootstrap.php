<?php

$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoloadCandidate) {
    if (file_exists($autoloadCandidate)) {
        require_once $autoloadCandidate;
        break;
    }
}

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/Models/Post.php';

if (! function_exists('wncms')) {
    function wncms(): object
    {
        static $wncms;

        return $wncms ??= new class
        {
            public function getModelClass(string $key): string
            {
                return match ($key) {
                    'tag' => config('wncms-tags.tag_model', \Wncms\Tags\Tag::class),
                    default => throw new InvalidArgumentException("Unsupported model key [{$key}] in test stub."),
                };
            }
        };
    }
}

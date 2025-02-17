<?php

declare(strict_types=1);

namespace xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\result;

use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\matze\pathfinder\node\Node;

class PathResult {
    /** @var Node[]  */
    public array $nodes = [];

    public function getNodes(): array{
        return $this->nodes;
    }

    public function addNode(Node $node): void {
        $this->nodes[$node->getHash()] = $node;
    }

    public function shiftNode(): ?Node {
        return array_shift($this->nodes);
    }
}
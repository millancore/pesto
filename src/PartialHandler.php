<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Exception\CompilerException;

trait PartialHandler
{
    protected array $sectionStack = [];

    /**
     * @var list<string>
     */
    protected array $slotStack = [];

    /**
     * @param array<string, mixed> $data
     */
    public function start(string $name, array $data = []): void
    {
        if (ob_start()) {
            $this->sectionStack[] = [
                'name' => $name,
                'data' => $data,
                'slots' => [],
            ];
        }
    }

    /**
     * @throws CompilerException
     * @throws \Throwable
     */
    public function end(): void
    {
        $content = ob_get_clean();
        $partial = array_pop($this->sectionStack);

        if ($partial === null) {
            throw new \Exception('Cannot end a section that was not started.');
        }

        // The content between start() and end() that is not in a slot() is the default slot.
        if (!isset($partial['data']['main'])) {
            $partial['data']['main'] = new Slot($content ?: '');
        }

        echo $this->renderer->render($this, $partial['name'], $partial['data']);
    }

    public function slot(string $name): void
    {
        if (ob_start()) {
            $this->slotStack[] = $name;
        }
    }

    /**
     * @throws \Exception
     */
    public function endSlot(): void
    {
        $content = ob_get_clean() ?: '';
        $slotName = array_pop($this->slotStack);

        if ($slotName === null) {
            throw new \Exception('Cannot end a slot that was not started.');
        }

        if (empty($this->sectionStack)) {
            throw new \Exception('Cannot define a slot outside of a section.');
        }

        $sectionIndex = count($this->sectionStack) - 1;
        $this->sectionStack[$sectionIndex]['slots'][$slotName] = new Slot($content);
        $this->sectionStack[$sectionIndex]['data'][$slotName] = new Slot($content);
    }
}

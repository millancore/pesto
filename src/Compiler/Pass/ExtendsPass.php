<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;
use Millancore\Pesto\Exception\CompilerException;

class ExtendsPass implements CompilerPassInterface
{

    /**
     * @throws CompilerException
     */
    public function compile(Pesto $pesto): void
    {
        $extendsNodes = $pesto->find('[php-extends]');

        if ($extendsNodes->isEmpty()) {
            return;
        }

        if($extendsNodes->count() > 1) {
            throw new CompilerException('Only one [php-extends] tag is allowed per template.');
        }

        /** @var Node $node */
        $node = $extendsNodes->getIterator()->current();

        $extends = $node->getAttribute('php-extends');
        $node->removeAttribute('php-extends');

        $extendsNode = $node->createProcessingInstruction('php', '$__pesto->render("' . $extends . '", get_defined_vars()); ');

        $node->insertAfter($extendsNode);
    }
}
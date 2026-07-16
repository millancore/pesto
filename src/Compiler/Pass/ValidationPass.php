<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Exception\CompilerException;
use Millancore\Pesto\Pesto;

/**
 * Runs last: any directive attribute still present was not consumed by a
 * previous pass and would leak into the rendered HTML.
 */
class ValidationPass extends Pass implements CompilerPass
{
    private const array DIRECTIVES = ['if', 'elseif', 'else', 'foreach', 'partial', 'with', 'slot'];

    public function compile(Pesto $pesto): void
    {
        $selector = implode(', ', array_map(
            fn (string $directive) => $this->directiveSelector($directive),
            self::DIRECTIVES,
        ));

        $errors = [];

        $pesto->find($selector)->each(function (Node $node) use (&$errors) {
            foreach (self::DIRECTIVES as $directive) {
                $attribute = $this->getDirectiveAttributeName($node, $directive);

                if ($attribute === null) {
                    continue;
                }

                $tag = strtolower($node->getDomNode()->nodeName);

                $errors[] = $directive === 'else' || $directive === 'elseif'
                    ? sprintf('Orphan "%s" directive on <%s>: it must be an immediate sibling of a "php-if" element.', $attribute, $tag)
                    : sprintf('Unprocessed "%s" directive on <%s>.', $attribute, $tag);
            }
        });

        if ($errors !== []) {
            throw new CompilerException(implode("\n", $errors));
        }
    }
}

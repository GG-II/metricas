<?php
/**
 * Parsedown - Markdown Parser en PHP
 * Versión simplificada incluida directamente
 * Fuente: https://github.com/erusev/parsedown
 */

namespace App\Utils;

class Parsedown
{
    const version = '1.7.4';

    function text($text)
    {
        // Procesar bloques de código con ``` primero
        $text = preg_replace_callback('/```([a-z]*)\n(.*?)\n```/s', function($matches) {
            $lang = $matches[1];
            $code = htmlspecialchars($matches[2]);
            return "\n<pre><code class=\"language-$lang\">$code</code></pre>\n";
        }, $text);

        $Elements = $this->textElements($text);
        $markup = $this->elements($Elements);
        $markup = trim($markup, "\n");
        return $markup;
    }

    protected function textElements($text)
    {
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        $text = str_replace("\t", '    ', $text);
        $text = rtrim($text, "\n");

        $lines = explode("\n", $text);
        $Elements = array();

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }
            $Elements[] = $this->line($line);
        }

        return $Elements;
    }

    protected function line($text)
    {
        // Headers
        if (preg_match('/^(#{1,6})\s+(.+?)$/', $text, $matches)) {
            $level = strlen($matches[1]);
            return array(
                'name' => 'h' . $level,
                'text' => $matches[2],
            );
        }

        // Code inline (PRIMERO, antes que otros formateos)
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);

        // Bold
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);

        // Italic
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_(.+?)_/', '<em>$1</em>', $text);

        // Links
        $text = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $text);

        // Images
        $text = preg_replace('/!\[(.+?)\]\((.+?)\)/', '<img src="$2" alt="$1">', $text);

        // Lists
        if (preg_match('/^[\*\-\+]\s+(.+)$/', $text, $matches)) {
            return array(
                'name' => 'li',
                'text' => $matches[1],
            );
        }

        // Ordered lists
        if (preg_match('/^\d+\.\s+(.+)$/', $text, $matches)) {
            return array(
                'name' => 'li',
                'text' => $matches[1],
                'ordered' => true,
            );
        }

        // Blockquote
        if (preg_match('/^>\s+(.+)$/', $text, $matches)) {
            return array(
                'name' => 'blockquote',
                'text' => $matches[1],
            );
        }

        // Horizontal rule
        if (preg_match('/^([-*_])\1{2,}$/', $text)) {
            return array('name' => 'hr');
        }

        // Sintaxis especial para gráficos: {{grafico:123}}
        $text = preg_replace_callback('/\{\{grafico:(\d+)\}\}/', function($matches) {
            return '<div class="grafico-placeholder" data-grafico-id="' . $matches[1] . '">[Gráfico #' . $matches[1] . ']</div>';
        }, $text);

        return array(
            'name' => 'p',
            'text' => $text,
        );
    }

    protected function elements(array $Elements)
    {
        $markup = '';

        foreach ($Elements as $Element) {
            $markup .= $this->element($Element);
        }

        return $markup;
    }

    protected function element(array $Element)
    {
        if (!isset($Element['name'])) {
            return $Element['text'] ?? '';
        }

        $name = $Element['name'];
        $text = $Element['text'] ?? '';

        if ($name === 'hr') {
            return '<hr>';
        }

        return '<' . $name . '>' . $text . '</' . $name . '>' . "\n";
    }
}

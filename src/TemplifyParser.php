<?php

namespace jensostertag\Templify;

use XMLReader;

class TemplifyParser {
    private XMLReader $xmlReader;

    private array $includeDetails = [
        "isInInclude" => false,
        "includeName" => "",
        "includeDepth" => 0,
        "includeContent" => ""
    ];

    public function __construct(string $template, string $slot = "") {
        $template = str_replace("<templify-slot />", $slot, $template);
        $this->xmlReader = XMLReader::XML($template);
    }

    public function parse(): string {
        $parsedContent = "";

        while($this->xmlReader->read() !== false) {
            switch($this->xmlReader->nodeType) {
                case XMLReader::END_ELEMENT:
                case XMLReader::ELEMENT:
                    $parsedContent .= $this->parseNode();
                    break;
                case XMLReader::TEXT:
                    $parsedContent .= $this->parseText();
                    break;
            }
        }

        return $parsedContent;
    }

    private function parseNode(): string {
        $nodeName = $this->xmlReader->name;
        $nodeDepth = $this->xmlReader->depth;
        $isClosingTag = $this->xmlReader->nodeType === XMLReader::END_ELEMENT;
        $isEmptyTag = $this->xmlReader->isEmptyElement;
        $attributes = [];
        while($this->xmlReader->moveToNextAttribute()) {
            $attributes[$this->xmlReader->name] = $this->xmlReader->value;
        }

        $isTemplifyTag = str_starts_with($nodeName, "templify-");

        $isTemplifyOpeningTag = $isTemplifyTag && !$isClosingTag;
        if(!$this->includeDetails["isInInclude"] && $isTemplifyOpeningTag) {
            // The include tag is opened here
            $this->includeDetails["isInInclude"] = true;
            $this->includeDetails["includeName"] = $nodeName;
            $this->includeDetails["includeDepth"] = $nodeDepth;
            $this->includeDetails["includeContent"] = "";

            if(!$isEmptyTag) {
                return "";
            }
        }

        $isTemplifyClosingTag = $isTemplifyTag && ($isClosingTag || $isEmptyTag) && $nodeName === $this->includeDetails["includeName"] && $nodeDepth == $this->includeDetails["includeDepth"];
        if($this->includeDetails["isInInclude"] && $isTemplifyClosingTag) {
            // The include tag is closed here
            $this->includeDetails["isInInclude"] = false;
            $includeName = ucfirst(str_replace("templify-", "", $this->includeDetails["includeName"]));
            $includeContent = $this->includeDetails["includeContent"];
            $this->includeDetails["includeName"] = "";
            $this->includeDetails["includeDepth"] = 0;
            $this->includeDetails["includeContent"] = "";

            // Parse the component
            // TODO: Parse component with included content
            return "";
        }

        if($this->includeDetails["isInInclude"]) {
            // The include tag has already been opened
            // Append the content to the include list without any changes
            $this->includeDetails["includeContent"] .= $this->parseNodeForSlot($nodeName, $isClosingTag, $isEmptyTag, $attributes);
            return "";
        }

        // TODO: The node shouldn't be parsed for the slot, but rather
        $parsedContent = $this->parseNodeForSlot($nodeName, $isClosingTag, $isEmptyTag, $attributes);

        if($nodeName === "foreach") {
            if(!$isClosingTag) {
                $parsedContent = "<?php foreach({$attributes["iterate"]} as {$attributes["key"]} => {$attributes["value"]}): ?>" . PHP_EOL;
            } else {
                $parsedContent = "<?php endforeach; ?>" . PHP_EOL;
            }
        }

        if($nodeName === "if") {
            if(!$isClosingTag) {
                $parsedContent = "<?php if({$attributes["condition"]}: ?>" . PHP_EOL;
            } else {
                $parsedContent = "<?php endif; ?>" . PHP_EOL;
            }
        }

        return $parsedContent;
    }

    private function parseNodeForSlot(string $nodeName, bool $isClosingTag, bool $isEmptyTag, array $attributes): string {
        $parsedContent = "<";
        if($isClosingTag) {
            $parsedContent .= "/";
        }
        $parsedContent .= $nodeName;
        if(!$isClosingTag) {
            foreach($attributes as $key => $value) {
                $parsedContent .= " {$key}=\"{$value}\"";
            }
        }
        if($isEmptyTag) {
            $parsedContent .= " /";
        }
        $parsedContent .= ">" . PHP_EOL;
        return $parsedContent;
    }

    private function parseText(): string {
        $textContent = $this->xmlReader->readouterXml();
        if($this->includeDetails["isInInclude"]) {
            $this->includeDetails["includeContent"] .= $textContent;
            return "";
        }

        return $this->xmlReader->readOuterXml();
    }
}

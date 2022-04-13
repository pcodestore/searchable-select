<?php

namespace Pcode\SearchableSelect;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResolvesReverseRelation;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Fields\Searchable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class SearchableSelect extends Field
{
    use Searchable, ResolvesReverseRelation;

    public $visible = true;
    public $readonly = false;

    public $labelPrefix = '';
    public $label = '';

    /**
     * The debounce amount to use when searching this field.
     *
     * @var int
     */
    public $debounce = 500;

    /**
     * The name of the Eloquent "belongs to" relationship.
     *
     * @var string
     */
    public $belongsToRelationship;

     /**
     * The displayable singular label of the relation.
     *
     * @var string
     */
    public $singularLabel;

    /**
     * The key of the related Eloquent model.
     *
     * @var string
     */
    public $belongsToId;

    /**
     * The column that should be displayed for the field.
     *
     * @var \Closure
     */
    public $display;

    /**
     * Indicates whether the field should display the "With Trashed" option.
     *
     * @var bool
     */
    public $displaysWithTrashed = true;

    /**
     * The class name of the related resource.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The URI key of the related resource.
     *
     * @var string
     */
    public $resourceName;


     /**
     * Indicates if the related resource can be viewed.
     *
     * @var bool
     */
    public $viewable = true;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'searchable-select';

    public function resource($name)
    {
        // Find searchable resource based on name if it's a class
        if (class_exists($name)) {
            $name = $name::uriKey();
            $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);
            $this->resourceClass = $resource;
        }

        $meta = [
            "searchableResource" => $name
        ];

        if (!isset($this->meta["label"])) {
            $resource = Nova::resourceForKey($name);
            $this->resourceClass = $resource;
            $meta["label"] = $resource::$title;
        }

        return $this->withMeta($meta);
    }

    public function labelPrefix(string $labelPrefix) {
        $this->labelPrefix = $labelPrefix;
        return $this;
    }

    public function label(string $label) {
        $this->label = $label;
        return $this;
    }

    public function value($value)
    {
        return $this->withMeta([
            "valueField" => $value
        ]);
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return array_merge([
            'belongsToId' => $this->belongsToId,
            'belongsToRelationship' => $this->belongsToRelationship,
            'debounce' => $this->debounce,
            'displaysWithTrashed' => $this->displaysWithTrashed,
            'label' => forward_static_call([$this->resourceClass, 'label']),
            'resourceName' => $this->resourceName,
            'reverse' => false,
            'searchable' => $this->searchable,
            'withSubtitles' => $this->withSubtitles,
            'showCreateRelationButton' => false,
            'singularLabel' => $this->singularLabel,
            'viewable' => $this->viewable,
            'labelPrefix' => $this->labelPrefix,
        ], parent::jsonSerialize());
    }
}

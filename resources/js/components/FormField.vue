<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :show-help-text="showHelpText"
  >
    <template #field>
      <div class="flex items-center">
        <SearchInput
          v-if="isSearchable && !isLocked && !currentlyIsReadonly"
          :data-testid="`${field.resourceName}-search-input`"
          @input="performSearch"
          @clear="clearSelection"
          @selected="selectResource"
          :error="hasError"
          :debounce="currentField.debounce"
          :value="selectedResource"
          :data="availableResources"
          :clearable="currentField.nullable"
          trackBy="value"
          class="w-full"
        >
          <div v-if="selectedResource" class="flex items-center">
            <div v-if="selectedResource.avatar" class="mr-3">
              <img
                :src="selectedResource.avatar"
                class="w-8 h-8 rounded-full block"
              />
            </div>

            {{ selectedResource.display }}
          </div>

          <template #option="{ selected, option }">
            <div class="flex items-center">
              <div v-if="option.avatar" class="mr-3">
                <img :src="option.avatar" class="w-8 h-8 rounded-full block" />
              </div>

              <div>
                <div
                  class="text-sm font-semibold leading-normal"
                  :class="{ 'text-white dark:text-gray-900': selected }"
                >
                  {{ option.display }}
                </div>

                <div
                  v-if="currentField.withSubtitles"
                  class="text-xs font-semibold leading-normal text-gray-500"
                  :class="{ 'text-white dark:text-gray-700': selected }"
                >
                  <span v-if="option.subtitle">{{ option.subtitle }}</span>
                  <span v-else>{{ __("No additional information...") }}</span>
                </div>
              </div>
            </div>
          </template>
        </SearchInput>

        <SelectControl
          v-if="!isSearchable || isLocked || currentlyIsReadonly"
          class="w-full"
          :select-classes="{ 'form-input-border-error': hasError }"
          :data-testid="`${field.resourceName}-select`"
          :dusk="field.attribute"
          :disabled="isLocked || currentlyIsReadonly"
          :options="availableResources"
          v-model:selected="selectedResourceId"
          @change="selectResourceFromSelectControl"
          label="display"
        >
          <option value="" selected :disabled="!currentField.nullable">
            {{ placeholder }}
          </option>
        </SelectControl>

       
      </div>

    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from "laravel-nova";
// import _ from "lodash";
import { onMounted } from "vue";

import find from 'lodash/find'
import isNil from 'lodash/isNil'

import DependentFormField from '../../../../../vendor/laravel/nova/resources/js/mixins/DependentFormField';
import PerformsSearches from '../../../../../vendor/laravel/nova/resources/js/mixins/PerformsSearches'


export default {
  mixins: [FormField, HandlesValidationErrors,DependentFormField,PerformsSearches],

  props: {
    resourceName: String,
    resourceId: String,
    field: Object,
    viaResource: {},
    viaResourceId: {},
    viaRelationship: {}
  },

  setup() {
    // console.log('Setup 111111', this);
    // onMounted(() => {
    //   console.log("mounted in the composition api!");
    //   this.initializeComponent();
    //   this.$nextTick(this.loadResourcesOnNew);
    // });

  },

  /**
   * Mount the component.
   */
  mounted() {
    this.initializeComponent()
  },


  data: () => ({
    availableResources: [],
    initializingWithExistingResource: false,
    selectedResources: [],
    selectedResource: null,
    selectedResourceId: null,
    search: "",
    searching: false,
    visible: true
  }),


  computed: {

    /**
     * Determine if the field is in readonly mode
     */
    // currentField() {
    //   return this.syncedField || this.field
    // },

    /**
     * Determine if the field is in readonly mode
     */
    // currentlyIsReadonly() {
    //   if (this.syncedField !== null) {
    //     return Boolean(
    //       this.syncedField.readonly ||
    //         get(this.syncedField, 'extraAttributes.readonly')
    //     )
    //   }

    //   return Boolean(
    //     this.field.readonly || get(this.field, 'extraAttributes.readonly')
    //   )
    // },

    selectedResourcesIds() {
      let ids = [];
      this.selectedResources.forEach(r => {
        ids.push(r[this.field.valueField]);
      });

      return ids;
    },
    /**
     * Determine if we are editing and existing resource
     */
    editingExistingResource() {
      return Boolean(this.field.value);
    },

    /**
     * Determine if we should select an initial resource when mounting this field
     */
    shouldSelectInitialResource() {
      return Boolean(this.editingExistingResource);
    },

    /**
     * Get the query params for getting available resources
     */
    queryParams() {
      console.log('sdjfkjdsklfj', this.field);
      return {
        params: {
          search: this.search,
          searchable: this.field.searchable == true ? 1 : 0,
          label: this.field.label,
          labelPrefix: this.field.labelPrefix,
          value: this.field.valueField
        }
      };
    },

    isLocked() {
      return Boolean(
        this.viaResource == this.field.resourceName && this.field.reverse
      );
    },
    isReadonly() {
      return (
        this.field.readonly || _.get(this.field, "extraAttributes.readonly")
      );
    },
    /**
     * Determine if the related resources is searchable
     */
    isSearchable() {
      return Boolean(this.currentField.searchable);
    },
    /**
     * Return the placeholder text for the field.
     */
    placeholder() {
      return this.currentField.placeholder || this.__('—')
    },
  },

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.field.value || "";
    },

    existingResourceIndex(value) {
      let index = -1;
      this.selectedResources.forEach((r, i) => {
        if (r[this.field.valueField] == value[this.field.valueField]) {
          index = i;
        }
      });

      return index;
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      if (this.field.isMultiple) {
        formData.append(
          this.field.attribute,
          JSON.stringify(this.selectedResourcesIds)
        );
      } else {
        formData.append(
          this.field.attribute,
          this.selectedResource ? this.selectedResource.value : ""
        );
      }
    },

    initializeComponent() {
      console.log('initializeComponent');
      if (this.editingExistingResource) {
        this.initializingWithExistingResource = true;
        this.selectedResourceId = this.field.value;
      }

      if (this.shouldSelectInitialResource) {
        this.getAvailableResources("", true, null).then(() =>
          this.selectInitialResource()
        );
      }

      this.field.fill = this.fill;
      this.field.readonly = false;
    },
    /**
     * Select the initial selected resource
     */
    selectInitialResource() {
      if (this.field.isMultiple) {
        this.availableResources.forEach(r => {
          if (this.field.value.includes(r[this.field.valueField])) {
            this.selectedResources.push(r);
          }
        });
      } else {
        this.selectedResource = _.find(
          this.availableResources,
          r => r[this.field.valueField] == this.selectedResourceId
        );
      }
    },
    /**
     * When not editing and loadsResourcesOnNew is enable we preload a list of resources
     */
    loadResourcesOnNew() {
      if (
        "loadResourcesOnNew" in this.field &&
        this.field.loadResourcesOnNew &&
        !this.editingExistingResource
      ) {
        this.search = "";
        this.getAvailableResources("");
      }
    },

    removeResource(value) {
      this.selectedResources.splice(this.existingResourceIndex(value), 1);
    },
    selectResource(value) {
      if (this.field.isMultiple) {
        if (this.existingResourceIndex(value) > -1) {
          this.removeResource(value);
        } else {
          this.selectedResources.push(value);
        }
        this.selectedResource = null;
        this.selectedResourceId = null;
        this.search = "";
      } else {
        this.selectedResource = value;
        this.selectedResourceId = value[this.field.valueField];
      }
    },

    /**
     * Select a resource using the <select> control
     */
    selectResourceFromSelectControl(e) {
      this.selectedResourceId = e.target.value;
      this.selectInitialResource();
    },

    /**
     * Get the resources that may be related to this resource.
     */
    getAvailableResources(query, use_resource_ids, max) {
      console.log('getAvailableResources');
      this.searching = true;
      this.availableResources = [];

      let params = this.queryParams;
      if (max !== null) {
        params.params.max = this.field.max;
      }

      if (this.field.isMultiple) {
        let resourceIds = [];
        if (this.selectedResourcesIds.length > 0) {
          resourceIds = this.selectedResourcesIds;
        } else if (this.selectedResourceId !== null) {
          resourceIds = JSON.parse(this.selectedResourceId);
        }

        params.params.resource_ids = JSON.stringify(resourceIds);
        if (use_resource_ids !== undefined) {
          params.params.use_resource_ids = true;
        } else {
          params.params.ignore_resource_ids = true;
        }
      } else {
        if (this.selectedResourceId !== null) {
          params.params.resource_ids = JSON.stringify([
            this.selectedResourceId
          ]);
          if (use_resource_ids) {
            params.params.use_resource_ids = true;
          }
        }
      }
      return Nova.request()
        .get(
          `/nova-vendor/pcode-searchable-select/${this.field.searchableResource}`,
          params
        )
        .then(response => {
          // Turn off initializing the existing resource after the first time
          this.initializingWithExistingResource = false;
          this.availableResources = response.data.resources;
          this.searching = false;
        });
    },

    /**
     * Determine if the relatd resource is soft deleting.
     */
    determineIfSoftDeletes() {
      return storage
        .determineIfSoftDeletes(this.field.resourceName)
        .then(response => {
          this.softDeletes = response.data.softDeletes;
        });
    },

    /**
     * Determine if the given value is numeric.
     */
    isNumeric(value) {
      return !isNaN(parseFloat(value)) && isFinite(value);
    }

    //end of methods()
  }
};
</script>

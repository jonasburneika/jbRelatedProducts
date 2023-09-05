<div class="row mt-3">
    <div class="col-md-8" id="supplier_collection">
        <div>
            <h2>{l s='Related products' mod='jbrelatedproducts'}</h2>
            <div class="row mb-1">
                <div class="col-md-12">
                    <div class="alert expandable-alert alert-info" role="alert">
                        <button type="button" class="read-more btn-link" data-toggle="collapse" data-target="#relatedProductsDesc" aria-expanded="false" aria-controls="collapseDanger">
                            {l s='Read more' d='Shop.Theme.Global'}
                        </button>
                        <p class="alert-text">
                            {l s='With our similar items feature, which was created to improve your shopping experience and assist you in finding the ideal complements to your purchase, you may explore a selected selection of complementary products.' mod='jbrelatedproducts'}
                        </p>
                        <div class="alert-more collapse" id="relatedProductsDesc">
                            <p>
                                {l s='Discover a seamless shopping experience as you quickly look through items that perfectly match your choices with our similar products technology, which enhances your enjoyment of online shopping.' mod='jbrelatedproducts'}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default mt-3">
                <div class="panel-body">
                    <div>
                        <fieldset class="form-group">
                            <div class="autocomplete-search"
                                 data-formid="jb_related_products"
                                 data-fullname="jb_related_products"
                                 data-mappingvalue="id"
                                 data-mappingname="name"
                                 data-remoteurl="{$remote_url}"
                                 id="jb_related_products_block"
                                 data-limit="0">


                                <div class="search search-with-icon">
                                    <span class="twitter-typeahead" style="position: relative; display: inline-block;">
                                        <input type="text" id="jb_related_products"
                                               class="form-control search typeahead jb_related_products tt-input"
                                               placeholder="{$placeholder}"
                                               autocomplete="off"
                                               spellcheck="false"
                                               dir="auto"
                                               style="position: relative; vertical-align: top;">
                                        <pre aria-hidden="true" style="position: absolute; visibility: hidden; white-space: pre; font-family: OpenSans-Regular, Helvetica, Verdana, Arial, sans-serif; font-size: 14px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: optimizelegibility; text-transform: none;"></pre>
                                        <div class="tt-menu" style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;">
                                            <div class="tt-dataset tt-dataset-3"></div>
                                        </div>
                                    </span>
                                </div>
                                <small class="form-text text-muted text-right typeahead-hint"></small>
                                <ul id="jb_related_products-data" class="typeahead-list nostyle col-sm-12 product-list">
                                    {foreach from=$products item=product}
                                        <li class="media">
                                            <div class="media-left">
                                                <img class="media-object image" src="{$product.image}">
                                            </div>
                                            <div class="media-body media-middle">
                                                <span class="label">{$product.name}</span><i class="material-icons delete">clear</i>
                                            </div>
                                            <input type="hidden" name="jb_related_products[data][]" value="{$product.id}">
                                        </li>
                                    {/foreach}
                                </ul>
                                <div class="invisible" id="tplcollection-jb_related_products">
                                    <span class="label">%s</span><i class="material-icons delete">clear</i>
                                </div>
                            </div>
                            <script type="text/javascript">
                                $('#jb_related_products').on('focusout', function resetSearchBar() {
                                    $('#jb_related_products').typeahead('val', '');
                                });
                            </script>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

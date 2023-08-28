{if isset($menu) && $menu}
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <ul class="nav navbar-nav">
                {foreach from=$menu item=menuItem}
                    <li class="nav-item{if $menuItem.current} active{/if}">
                        <a class="nav-link" href="{$menuItem.url|escape:'htmlall':'UTF-8'}">
                            <i class="{$menuItem.icon|escape:'htmlall':'UTF-8'}"></i>
                            {$menuItem.title|escape:'htmlall':'UTF-8'}
                        </a>
                    </li>
                {/foreach}
            </ul>
            <div class="clearfix"></div>
        </div>
    </nav>
{/if}

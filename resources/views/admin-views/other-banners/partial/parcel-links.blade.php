<div class="d-flex flex-wrap justify-content-between align-items-center mb-20 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/promotional-banner/add-new') || Request::is('admin/promotional-banner/edit*') ? 'active' : '' }}"
                href="{{ route('admin.promotional-banner.add-new') }}">{{'pancartas promocionales'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/promotional-banner/add-video') ? 'active' : '' }}"
                href="{{ route('admin.promotional-banner.add-video') }}">{{'video'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/promotional-banner/add-why-choose') ||  Request::is('admin/promotional-banner/why-choose/edit*') ? 'active' : '' }}"
                href="{{ route('admin.promotional-banner.add-why-choose') }}">{{'¿Por qué elegirnos?'}}</a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
</div>
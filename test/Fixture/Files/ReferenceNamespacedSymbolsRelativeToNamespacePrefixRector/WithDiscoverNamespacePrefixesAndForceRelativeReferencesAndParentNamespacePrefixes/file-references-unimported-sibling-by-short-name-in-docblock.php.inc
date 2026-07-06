<?php

namespace App\Catalog\Product\Listing;

final class ProductListing
{
    /**
     * @var list<ProductSummary>
     */
    private array $items;

    /**
     * @param ProductSummary $item
     * @param int $index
     * @param array{price: Price, index: int} $shape
     *
     * @return self
     */
    public function with(ProductSummary $item, int $index, array $shape): self
    {
        return $this;
    }

    public function total(): \App\Catalog\Product\Listing\Price
    {
        return \App\Catalog\Product\Listing\Price::sum();
    }
}

?>
-----
<?php

namespace App\Catalog\Product\Listing;

use App\Catalog;
final class ProductListing
{
    /**
     * @var list<Catalog\Product\Listing\ProductSummary>
     */
    private array $items;

    /**
     * @param Catalog\Product\Listing\ProductSummary $item
     * @param int $index
     * @param array{price: Catalog\Product\Listing\Price, index: int} $shape
     *
     * @return self
     */
    public function with(Catalog\Product\Listing\ProductSummary $item, int $index, array $shape): self
    {
        return $this;
    }

    public function total(): Catalog\Product\Listing\Price
    {
        return Catalog\Product\Listing\Price::sum();
    }
}

?>

<?php


namespace Http\Controllers\Product;

use App\Jobs\ProcessProductsJob;
use App\Models\Account;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    use DatabaseMigrations;
    /** @var Product  */
    public $product;
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @group Product_IndexController
     */
    public function testGetProductById()
    {

        $this->product = Product::factory()->create(
            [
                'code' => 'code1',
                'name' => 'product 1',
                'description' => 'description for product 1'
            ]
        );

        $response = $this->get('api/products/'.$this->product->id);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Product found',
            'data'    =>
                [
                    'id' => $this->product->id,
                    'code' => $this->product->code,
                    'name' => $this->product->name,
                    'description' => $this->product->description,
                ],
        ]);

        //test product not found
        $response = $this->get('api/products/50');
        $response->assertStatus(404);
    }

    /**
     * @test
     * @group Product_IndexController
     */
    public function testGetAllProducts()
    {

        $this->product = Product::factory()->create(
            [
                'code' => 'code1',
                'name' => 'product 1',
                'description' => 'description for product 1'
            ]
        );
        $this->stock = Stock::factory()->create(
            [
                'product_id' => $this->product->id,
                'on_hand' => 5,
                'taken' => 1
            ]
        );
        $this->product2 = Product::factory()->create(
            [
                'code' => 'code2',
                'name' => 'product 2',
                'description' => 'description for product 2'
            ]
        );
        $this->stock2 = Stock::factory()->create(
            [
                'product_id' => $this->product2->id,
                'on_hand' => 3,
                'taken' => 2
            ]
        );
        $response = $this->get('api/products');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'All Products',
            'data'    => [
                'current_page' => 1,
                'data' => [
                    [
                        'id' => $this->product->id,
                        'code' => $this->product->code,
                    ],
                    [
                        'id' => $this->product2->id,
                        'code' => $this->product2->code,
                    ],
                ]
            ]
        ]);

        $response = $this->get('api/products?stock=true&sortBy=on_hand&sortType=desc');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'All Products',
            'data'    => [
                'current_page' => 1,
                'data' => [
                    [
                        'id' => $this->product->id,
                        'code' => $this->product->code,
                        'on_hand' => 5,
                        'taken' => 1
                    ],
                    [
                        'id' => $this->product2->id,
                        'code' => $this->product2->code,
                        'on_hand' => 3,
                        'taken' => 2
                    ],
                ]
            ]
        ]);

        //test product not found
        $response = $this->get('api/products/50');
        $response->assertStatus(404);
    }

    /**
     * @test
     * @group Product_IndexController
     */
    public function testGetAllProductDetails()
    {

        $this->product = Product::factory()->create(
            [
                'code' => 'code1',
                'name' => 'product 1',
                'description' => 'description for product 1'
            ]
        );
        $this->stock = Stock::factory()->create(
            [
                'product_id' => $this->product->id,
                'on_hand' => 5,
                'taken' => 1
            ]
        );

        $response = $this->get('api/products/'.$this->product->id.'/details');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'All Product Details',
            'data'    => [
                "id" => $this->product->id,
                "code" => $this->product->code,
                "name" => $this->product->name,
                "description" => $this->product->description,
                'on_hand' => $this->stock->on_hand,
                'taken' => $this->stock->taken
                ]
        ]);
    }

    /**
     * @test
     * @group Product_IndexController
     */
    public function testDeleteAProduct()
    {
        $this->product = Product::factory()->create(
            [
                'code' => 'code1',
                'name' => 'product 1',
                'description' => 'description for product 1'
            ]
        );

        $response = $this->delete('api/products/'.$this->product->id);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * @test
     * @group Product_IndexController
     */
    public function testImportProducts()
    {
        $response = $this->post('api/products/import');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => [
                'file' => [
                    'The file field is required.'
                ]
            ],
        ]);

        Storage::fake('uploads');

        $header = 'code,name,description';
        $row1 = '229113,B-ED PIZZLES BP,B-ED PIZZLES BP';
        $row2 = '4030,value 2,value 3';

        $content = implode("\n", [$header, $row1, $row2]);

        $inputs = [
            'csv_file' =>
                UploadedFile::
                fake()->
                createWithContent(
                    'test.csv',
                    $content
                )
        ];

        $this->expectsJobs(ProcessProductsJob::class);
        $response = $this->post('api/products/import',['file' => $inputs['csv_file']]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'File saved'
        ]);
    }

    /**
     * @test
     * @group Product_IndexController
     */
    public function testUpdateAProduct()
    {
        $this->product = Product::factory()->create(
            [
                'code' => 'code1',
                'name' => 'product 1',
                'description' => 'description for product 1'
            ]
        );

        $data = [
            'code' => 'code2',
            'name' => 'product 2',
            'description' => 'description for product 2'
        ];

        $response = $this->put('api/products/'.$this->product->id, $data);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Product updated successfully',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'code' => 'code2',
            'name' => 'product 2',
            'description' => 'description for product 2'
        ]);
    }

    /**
     * @test
     * @group Product_IndexController
     */
    public function testStoreStockForAProduct()
    {
        $this->product = Product::factory()->create(
            [
                'code' => 'code1',
                'name' => 'product 1',
                'description' => 'description for product 1'
            ]
        );

        $data = [
            'onHand' => 6,
            'taken' => 2,
            'productionDate' => '14/05/2020'
        ];

        $response = $this->post('api/products/'.$this->product->id.'/stock', $data);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Stock created',
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'on_hand' => 6,
            'taken' => 2,
            'production_date' => '2020-05-14'
        ]);
    }

    /**
     * @test
     * @group Product_IndexController
     */
    public function testStoreAProduct()
    {
        $this->product = Product::factory()->create(
            [
                'code' => 'code1',
                'name' => 'product 1',
                'description' => 'description for product 1'
            ]
        );

        $data = [
            'code' => 'code1',
            'name' => 'product 1',
            'description' => 'description for product 1'
        ];

        $response = $this->post('api/products', $data);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => [
                'code' => 'code1',
                'name' => 'product 1',
                'description' => 'description for product 1'
            ]
        ]);
    }
}

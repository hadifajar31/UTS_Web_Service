<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller{

    protected $model = Products::class;
    protected $products, $product;

    public function index(Request $request) {
        $accHeader = $request->headers->get('Accept');
        if($accHeader === '*/*' || empty($accHeader) ||
            ($accHeader != 'application/json' && $accHeader != 'application/xml')) {
            return response('Not Accepttable', 404);
        }
        $this->products = $this->model::Where(['user_id' => Auth::user()->id])->OrderBy("id", "DESC")->paginate(2)->toArray();
        if($accHeader == 'application/json') {
            $response = [
                'total_count' => $this->products['total'],
                'limit' =>  $this->products['per_page'],
                'pagination' => [
                    'next_page' => $this->products['next_page_url'],
                    'prev_page' => $this->products['prev_page_url'],
                    'current_page' => $this->products['current_page'],
                ],
                'data' => $this->products['data']
            ];
            return response()->json($response, 200);
        }
        if($accHeader == 'application/xml') {
            $xml = new \SimpleXMLElement('<Products/>');
            foreach($this->products->items('data') as $item) {
                $xmlItem = $xml->addChild('Users');
                foreach ($item->getAttributes() as $key => $value) {
                    $xmlItem->addChild($key, $value);
                }
            }
            return $xml->asXML();
        }
    }

    public function show(Request $request, $id) {
        $accHeader = $request->headers->get('Accept');
        if($accHeader === '*/*' || empty($accHeader) ||
            ($accHeader != 'application/json' && $accHeader != 'application/xml')) {
            return response('Not Accepttable', 404);
        }

        $this->product = $this->model::find($id);
        if(!$this->product) {abort(404);}

        if($accHeader == 'application/json') {
            return response()->json($this->product, 200);
        }

        if($accHeader == 'application/xml') {
            $xml = new \SimpleXMLElement('<Products/>');
            foreach ($this->product->getAttributes() as $key => $value) {
                $xml->addChild($key, $value);
            }
            return $xml->asXML();
        }
    }

    public function store(Request $request) {
        $accHeader = $request->headers->get('Accept');
        $contentTypeHeader = $request->headers->get('Content-Type');

        if($accHeader === '*/*' || empty($accHeader) ||
            ($accHeader !== 'application/json' && $accHeader !== 'application/xml'
            && $contentTypeHeader !== 'application/json' && $contentTypeHeader !== 'application/xml')) {
            return response('Not Accepttable', 404);
        }

        $authUser = Auth::user();
// json
        if ($accHeader == 'application/json' && $contentTypeHeader == 'application/json') {
            $data = $request->all();
            if ($authUser->id !== (int)$data['user_id']) {
                return response()->json(['error' => 'Unauthorized action'], 401);
            }
            $validator = Validator::make($data, [
                'id' => 'required|numeric|exists:users',
                'brand_name'=> 'required|string',
                'product_name'=> 'required|string',
                'category' => 'required|in:sayur,buah,olahan_ayam,olahan_sapi,seafood,frozen_misc',
                'origin' => 'required|in:lokal,import',
                'price'=> 'required|numeric',
                'stock' => 'required|numeric',
            ]);
            if ($validator->fails()) return response()->json(['error' => $validator->errors()], 400);

            $this->product = new Products;
            $this->product->fill($data)->save();
            return response()->json($this->product, 200);
        }
            // xml
            if ($accHeader == 'application/xml' && $contentTypeHeader == 'application/xml') {
                $xmlString = $request->getContent();
                $xml = simplexml_load_string($xmlString);
                $data = json_decode(json_encode($xml), true);
                if ($authUser->id !== (int)$data['user_id']) {
                    $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>
                        <Error>
                            <code>401</code>
                            <message>Unauthorized action</message>
                        </Error>';
                    return response($xmlResponse, 401)->header('Content-Type', 'application/xml');
                }
                $this->product->addXmlData($data);
            $validator = Validator::make($data, [
                'id' => 'required|numeric|exists:users',
                'brand_name'=> 'required|string',
                'product_name'=> 'required|string',
                'category' => 'required|in:sayur,buah,olahan_ayam,olahan_sapi,seafood,frozen_misc',
                'origin' => 'required|in:lokal,import',
                'price'=> 'required|numeric',
                'stock' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>
                    <Error>
                        <code>400</code>
                        <message>' . implode(',', $validator->errors()->all()) . '</message>
                    </Error>';

                return response($xmlResponse, 400)->header('Content-Type', 'application/xml');
            }
            $this->product = new Products;
            $this->product->fill($data)->save();
            return response($xmlString, 200)->header('Content-Type', 'application/xml');
        }
    }

    public function update(Request $request, $id) {
        $accHeader = $request->headers->get('Accept');
        $contentTypeHeader = $request->headers->get('Content-Type');

        if($accHeader === '*/*' || empty($accHeader) ||
            ($accHeader != 'application/json' && $accHeader != 'application/xml'
            && $contentTypeHeader!= 'application/json' && $contentTypeHeader!= 'application/xml')) {
            return response('Not Accepttable', 404);
        }

        $this->product = $this->model::find($id);
        if(!$this->product) {abort(404);}
        // json
        if($accHeader == 'application/json' && $contentTypeHeader == 'application/json') {
            $data = $request->all();
            $validator = Validator::make($data, [
                'brand_name'=> 'required|string',
                'product_name'=> 'required|string',
                'category' => 'required|in:sayur,buah,olahan_ayam,olahan_sapi,seafood,frozen_misc',
                'origin' => 'required|in:lokal,import',
                'price'=> 'required|numeric',
                'stock' => 'required|numeric',
            ]);
            if ($validator->fails()) return response()->json(['error' => $validator->errors()], 400);

            $this->product->fill($data)->save();
            return response()->json($this->product, 200);
        }
        // xml
        if($accHeader == 'application/xml' && $contentTypeHeader == 'application/xml') {
            $xmlString = $request->getContent();
            $xml = simplexml_load_string($xmlString);
            $data = json_decode(json_encode($xml), true);

            $validator = Validator::make($data, [
                'brand_name'=> 'required|string',
                'product_name'=> 'required|string',
                'category' => 'required|in:sayur,buah,olahan_ayam,olahan_sapi,seafood,frozen_misc',
                'origin' => 'required|in:lokal,import',
                'price'=> 'required|numeric',
                'stock' => 'required|numeric',
            ]);
            if ($validator->fails()) return response()->json(['error' => $validator->errors()], 400);

            $this->product->fill($data)->save();
            $xml = new \SimpleXMLElement('<Products/>');
            foreach ($this->product->getAttributes() as $key => $value) 
            {
                $xml->addChild($key, $value);
            }
            return $xml->asXML();
        }
    }

    public function delete($id)
    {
        try {
            $product = Products::find($id);
    
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
    
            $product->delete();
    
            return response()->json(['message' => 'Product deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product', 'error' => $e->getMessage()], 500);
        }
    }
}

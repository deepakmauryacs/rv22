<?php
namespace App\Http\Controllers\Vendor;
use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use App\Models\ProductGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProductGalleryController extends Controller {
    
    // Method to show the product gallery page
    public function create(VendorProduct $product) {
        $product->load('gallery');
        return view('vendor.products.gallery', compact('product'));
    }

    public function store(Request $request, VendorProduct $product) {
        // Validate the images input field to ensure it's an array of strings (filenames)
        $request->validate(['images' => 'required|array', 'images.*' => 'string', // Validate that each item is a string (filename)
        ]);
        try {
            $disk = Storage::disk('public_uploads');
            $productDir = 'uploads/product';
            $tempDir = $productDir . '/temp';

            if (!$disk->exists($productDir)) {
                $disk->makeDirectory($productDir);
            }

            foreach ($request->input('images') as $filename) {
                $tempPath = $tempDir . '/' . $filename;
                $finalPath = $productDir . '/' . $filename;

                if (!$disk->exists($tempPath)) {
                    continue;
                }

                if (!$disk->move($tempPath, $finalPath)) {
                    throw new \RuntimeException('Unable to move image to product directory');
                }

                ProductGallery::create([
                    'product_id' => $product->id,
                    'image' => $filename,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Images uploaded successfully', ]);
        }
        catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error uploading images: ' . $e->getMessage(), ], 500);
        }
    }

    // Method to handle uploading temporary images
    public function uploadTemp(Request $request) {
        // Validate the uploaded files
        $request->validate(['images' => 'required', 'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', ]);
        $uploadedImages = [];
        try {
            $disk = Storage::disk('public_uploads');
            $tempDir = 'uploads/product/temp';

            if (!$disk->exists($tempDir)) {
                $disk->makeDirectory($tempDir);
            }

            foreach ($request->file('images') as $image) {
                // Generate a unique filename with timestamp and replace underscores with hyphens
                $extension = $image->getClientOriginalExtension();
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = strtolower(time() . '-' . str_replace('_', '-', $originalName)) . '.' . $extension;

                // Store the file within the public disk so later requests using Storage work correctly
                $path = $image->storeAs($tempDir, $filename, 'public_uploads');

                // Prepare the response with the temporary URL
                $uploadedImages[] = [
                    'name' => $filename,
                    'temp_url' => asset('public/' . $path),
                ];
            }
            return response()->json(['success' => true, 'images' => $uploadedImages, ]);
        }
        catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error uploading temporary images: ' . $e->getMessage(), ], 500);
        }
    }

    // Method to remove a temporary image
    public function removeTemp(Request $request) {
        $request->validate(['image' => 'required|string', ]);
        try {
            Storage::disk('public_uploads')->delete('uploads/product/temp/' . $request->image);
            return response()->json(['success' => true, 'message' => 'Image removed', ]);
        }
        catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error removing temporary image: ' . $e->getMessage(), ], 500);
        }
    }
    
    // Method to delete an image from the gallery
    public function destroy(Request $request, VendorProduct $product) {
        $request->validate(['image_id' => 'required|integer', 'image_name' => 'required|string', ]);
        try {
            Storage::disk('public_uploads')->delete(['uploads/product/' . $request->image_name]);
            ProductGallery::where('id', $request->image_id)->where('product_id', $product->id)->delete();
            return response()->json(['success' => true, 'message' => 'Image deleted successfully', ]);
        }
        catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting image: ' . $e->getMessage(), ], 500);
        }
    }
}

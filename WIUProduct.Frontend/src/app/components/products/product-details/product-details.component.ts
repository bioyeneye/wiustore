import {Component, OnInit} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import { Location } from '@angular/common';
import {FormBuilder, FormControl, FormGroup, Validators} from "@angular/forms";
import {Comment, Product} from "../../../models/product";
import {map} from "rxjs/operators";
import {ProductService} from "../../../services/product.service";
@Component({
  selector: 'app-product-details',
  templateUrl: './product-details.component.html',
  styleUrls: ['./product-details.component.scss']
})
export class ProductDetailsComponent implements OnInit {
  productId!: number;
  product!: Product;
  comments!: Comment[];

  reviewForm: FormGroup = new FormGroup({
    name: new FormControl(''),
    comment: new FormControl(''),
  });
  constructor(private location: Location, private route: ActivatedRoute,
              private formBuilder: FormBuilder,
              private productService: ProductService) {}
  ngOnInit() {
    this.reviewForm = this.formBuilder.group({
      name: ['', Validators.required],
      comment: ['', Validators.required]
    });

    this.getProduct();
  }

  getProduct() {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.productId = id;

    this.productService.getProduct(`${id}`)
      .subscribe((result)=>{
        if (result.data) {
          this.product = result.data;
        }
      });

    this.productService.getComments(`${id}`)
      .subscribe((result)=>{
        if (result.data) {
          this.comments = result.data;
        }
      });
  }
  submitReview() {
    console.log(this.reviewForm.value);
    const comment: Comment = {
      comment: this.reviewForm.value["comment"],
      name: this.reviewForm.value["name"],
      product_id: `${this.productId}`
    }
    this.productService.createComment(comment)
      .subscribe((result)=>{
        if (result.hasResult) {
          window.location.reload();
        }
      });
  }
  backClicked() {
    this.location.back();
  }
}
